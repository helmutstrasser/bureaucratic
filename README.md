# josefglatz/bureaucratic – basics for professional TYPO3 projects

# Backend

## Required fields for backend users

* realName (trim, required)
* email (trim, required, email)

### What the hell?

| Reason                       | Description                                                                    |
|------------------------------|--------------------------------------------------------------------------------|
| Security                     | Requiring realName and email motivates to add one backend user per real person |
| Base for TYPO3 core features | Forgot password functionality → reduces support cases                          |
| Backend UX                   | Better UX in backend views history, or record info modals → real name is shown |

## Required fields for tt_content

* header (trim, required)

### What the hell?

| Reason     | Description                                                                                                                         |
|------------|-------------------------------------------------------------------------------------------------------------------------------------|
| Backend UX | Requiring `tt_content.header` makes records with "title" `[No title]` in list view belongs to the past                              |
| Backend UX | `tt_content.header` with `tt_content.header_layout` set to "Hidden" `0` still allows disabled/hidden titles in the website frontend |