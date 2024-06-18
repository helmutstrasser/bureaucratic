<?php
declare(strict_types=1);

namespace JosefGlatz\Bureaucratic\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Abstract class for all CLI command classes
 */
class AbstractCommand extends Command
{
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);
    }

    protected function message(SymfonyStyle $io, bool $asJson, bool $success, string $message): bool
    {
        if ($asJson) {
            $io->writeln((string)\json_encode([
                'success' => $success,
                'message' => $message,
            ]));
        } elseif ($success) {
            $io->success($message);
        } else {
            $io->error($message);
        }
        return $success;
    }

    protected function validateEmailAddress($emailAddress): bool
    {
        return GeneralUtility::validEmail($emailAddress);
    }

}