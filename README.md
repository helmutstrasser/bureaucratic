# josefglatz/bureaucratic – basics for professional TYPO3 projects

Part of `professional_aspects`

---

# Backend

## Required fields for backend users

* realName (trim, required)
* email (trim, required, email)

### What the hell?

| Reason                       | Implemented | Description                                                                                                                                                                    | Implementation                                     |
|------------------------------|-------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|----------------------------------------------------|
| Security                     | yes         | Requiring realName and email fields motivates adding one backend user per real human                                                                                           | TCA/Overrides, by requiring email address and name |
| Security                     | yes         | Limit sharing bad passwords across multiple persons/teams/departments on customer side by                                                                                      | TCA/Overrides, by requiring email address          |
| Security                     |             | Write custom commands to check for not used TYPO3 backend users                                                                                                                |                                                    |
| Security                     |             | Write custom commands to delete TYPO3 backend users which are not used for a while                                                                                             |                                                    |
| Security                     |             | Implement custom commands to delete TYPO3 backend users based on deny lists                                                                                                    |                                                    |
| Reporting                    |             | Write custom commands to inform team members of not used backend editors                                                                                                       |                                                    |
| Base for TYPO3 core features |             | Forgot password functionality → reduces support cases                                                                                                                          |                                                    |
| Backend UX                   |             | Better UX in backend views history, or record info modals → real name is shown                                                                                                 |                                                    |
| Backend UX                   |             | Improve UX by adding profile image for each real human editor or use ext:gravatar for automatic profile images                                                                 |                                                    |
| Reflects real life           |             | Better overview of you much persons are really working as editors on the project                                                                                               |                                                    |
| More realistic backend stats |             | See who is really editing content in an TYPO3 instance. Base for building some custom user statistics. Imagine writing some statistics for the TYPO3 backend dashboard module. |                                                    |

## Required fields for tt_content

* header (trim, required)

### What the hell?

| Reason     | Implemented | Description                                                                                                                         |
|------------|-------------|-------------------------------------------------------------------------------------------------------------------------------------|
| Backend UX | yes         | Requiring `tt_content.header` makes records with "title" `[No title]` in list view belongs to the past                              |
| Backend UX | yes         | `tt_content.header` with `tt_content.header_layout` set to "Hidden" `0` still allows disabled/hidden titles in the website frontend |

## Disabled backend functionality

* No direct uploads in backend forms. An editor have to upload files within the
  TYPO3 filelist module

### What the hell?

Haven direct upload forms in backend forms motivates lazy backend editors to not
structure their assets (files). There are possibilities to add folders in the
uplaod UI but most of the users do not take care of this things.

---

# Frontend

## Required packages

* [`josefglatz/httpseverywhere`](https://github.com/josefglatz/httpseverywhere) (
  TYPO3 Middleware to force https as a
  last fallback)
