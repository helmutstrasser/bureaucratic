<?php
declare(strict_types=1);

namespace JosefGlatz\Bureaucratic\Commands\BackendUsers;

use Doctrine\DBAL\Exception;
use JosefGlatz\Bureaucratic\Commands\AbstractCommand;
use JosefGlatz\Bureaucratic\Services\BackendUserService;
use JosefGlatz\Bureaucratic\Services\EmailService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DeleteLongLastingDisabledUsersCommand extends AbstractCommand
{
    protected BackendUserService $backendUserService;
    private EmailService $emailService;

    public function __construct(
        BackendUserService $backendUserService,
        EmailService $emailService
    )
    {
        $this->backendUserService = $backendUserService;
        $this->emailService = $emailService;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Deletes long lasting disabled backend users');
        $this->addArgument('daysNotModified', InputArgument::REQUIRED, 'Minimum amount of days that a disabled user record was not modified', 5);
        $this->addArgument('group', InputArgument::OPTIONAL, 'User group filter for the backend users. Use a single number for a single backend user group id. If a single number is set, it will be handled as find_in_set. If content is in quotes (for example "1,42") it searches for exactly this string.', '');
        $this->addOption('dry-run', null, InputOption::VALUE_NONE, 'If set no changes to backend user record will be performed and no emails will be sent');
        $this->addOption('sendEmailToUsers', 'email', InputOption::VALUE_OPTIONAL, 'Inform affected users via email', false);
        $this->addArgument('sender', InputArgument::OPTIONAL, 'Sender email address of the emails (default set in TYPO3_CONF_VARS)', '');
        $this->addArgument('bcc', InputArgument::OPTIONAL, 'BCC email address', '');
        $this->addOption('sendReportEmail', null,InputOption::VALUE_NONE, 'Inform persons with a dedicated report about deleted records.');
        $this->addOption('reportEmailReceivesr', null, InputOption::VALUE_IS_ARRAY, 'Email recipients of the report about deleted records.');
        $this->addArgument('emailTemplatePathUser', InputArgument::OPTIONAL, 'Path of the email fluid template for affected backend users', 'EXT:bureaucratic/Resources/Private/Templates/Email/DeleteLongLastingDisabledUsers/MailToUser.html');
        $this->addArgument('emailTemplatePathReport', InputArgument::OPTIONAL, 'Path of the email fluid template for report recipients', 'EXT:bureaucratic/Resources/Private/Templates/Email/DeleteLongLastingDisabledUsers/ReportMail.html');

        // @todo: add support for option "sendEmailToCustomer" if a specific customer email receiver should be informed about deleted records
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $daysNotModified = $input->getArgument('daysNotModified');
        $group = $input->getArgument('group');
        $dryRun = $input->getOption('dry-run');

        $sendEmailToUsers = $input->getOption('sendEmailToUsers');
        $emailTemplatePathUser = $input->getArgument('emailTemplatePathUser');
        $sender = $input->getArgument('sender');
        $bcc = $input->getArgument('bcc');

        $sendReportEmail = $input->getOption('reportEmailReceiver');
        $reportEmailReceivers = $input->getOption('reportEmailReceiver');
        $emailTemplatePathReport = $input->getArgument('emailTemplatePathReport');

        // Check if all arguments and options are given – if not return 1

        $records = $this->backendUserService->getLongLastingDisabledUsers($daysNotModified, $group);
        $recordsCount = $records->rowCount();
        $reportDeletedRecords = [];

        // Early return if no record is affected by the query
        if ($recordsCount === 0) {
            return Command::SUCCESS;
        }

        // dry-run enabled: render amount of affected records and stop processing affected records
        if ($recordsCount > 0 && $dryRun) {
            $this->message($io, false, true, $recordsCount . ' backend user records affected. Re-execute command without dry-run option to delete them.');

            $table = new Table($output);
            $table
                ->setHeaders([
                    'UID',
                    'username',
                    'realName',
                    'email',
                    'description',
                ]);
            foreach ($records as $record) {
                $table->addRow([$record['uid'],
                    $record['username'],
                    $record['realName'],
                    $record['email'],
                    $record['description'],
                ]);
            }
            $table->render();

            return Command::SUCCESS;
        } else {
            $this->message($io, false, true, 'Found ' . $recordsCount . ' backend user records to mark as deleted.');
        }

        $progressBar = new ProgressBar($output, $recordsCount);
        $progressBar->start();
        $progressBar->setMessage('Mark affected user records as deleted...');

        // process results
        foreach ($records as $record) {
            $deleted = $this->backendUserService->deleteUserByUid($record['uid']);

            // and send mail to user if option is set (optionally with bcc recipients)
            if ($deleted > 0 && $sendEmailToUsers) {
                // check if email address is valid
                if ($this->validateEmailAddress(trim($record['email']))) {
                    // try to send email to user record
                    $this->emailService->sendEmailToLongLastingDisabledUser(
                        recipientEmailAddress: trim($record['email']),
                        recipientName: trim($record['realName']),
                        bccRecipients: $bcc,
                        senderEmailAddress: $sender,
                        templatePath: $emailTemplatePathUser,
                        record: $record,
                        emailLanguage: $record['language'] ?? 'default',
                    );
                }
            }

            if ($deleted) {
                $reportDeletedRecords[] = [
                    $record['uid'],
                    $record['username'],
                    $record['realName'],
                    $record['email'],
                    $deleted,
                ];
            }
            $progressBar->advance();
        }

        $progressBar->finish();

        // send reports mail if enabled
        if ($sendReportEmail && !empty($reportEmailReceivers) && !empty($reportDeletedRecords)) {
            $progressBar = new ProgressBar($output, count($reportEmailReceivers));
            $progressBar->start();
            $progressBar->setMessage('Sending report email');
            foreach ($reportEmailReceivers as $receiver) {
                if ($this->validateEmailAddress($receiver)) {
                    $this->emailService->sendReportEmailOfDeletedLongLastingDisabledUsers(
                        recipientEmailAddress: $receiver,
                        senderEmailAddress: $sender,
                        templatePath: $emailTemplatePathReport,
                        reportRecords: $reportDeletedRecords,
                        emailLanguage: 'default',
                    );
                }
                $progressBar->advance();
            }
            $progressBar->finish();
        }

        // write a logfile about modifications done

        return Command::SUCCESS;

    }


}