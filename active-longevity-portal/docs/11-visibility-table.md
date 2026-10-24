# Who can see what

*Generated from the rules the Portal enforces. Do not edit by hand:*
*change the declarations and run `php artisan portal:visibility-table` again.*

Four audiences, from most protected to least. A collaborator may always
choose to show less than the Institute permits, and never more.

- **The Institute only**
- **The collaborator and the Institute**
- **Admitted collaborators**
- **Anyone on the web**

Nothing is placed in the fourth. It exists so that if the Institute ever
decides something should be visible on the open web, that is a setting
rather than a rebuild.

## The Institute only

| Information | Who decides | Why this limit |
|---|---|---|
| Feedback they gave about the Portal | The Institute | Given to the Institute about the Portal, not about themselves. |
| City | The Institute | Collected as administrative, and said to be so at the time. |
| State or region | The Institute | Collected as administrative, and said to be so at the time. |
| Password | The Institute | Stored only as an irreversible hash and never displayed to anybody. |
| Two-factor secret | The Institute | A security credential, never shown. |
| How they came to the Institute | The Institute | Provenance is the Institute's record, not a public fact. |
| Who recommended them | The Institute | Named to the invited person by the Institute if it chooses, never published. |
| The referrer's relationship to them | The Institute | A private statement by one person about another; seen by the Institute alone, permanently. |
| How the referrer knows them | The Institute | Given in confidence to inform a decision. |
| Why they applied | The Institute | Written for the admission decision, not for publication. |
| Review decisions and reasoning | The Institute | The Institute's own judgments. |

## The collaborator and the Institute

| Information | Who decides | Why this limit |
|---|---|---|
| Interests registered in working with them | The Institute | Seen by the two people concerned and the Institute, which passes it on. Never a public count. |
| Email address | The Institute | The Portal carries messages rather than displaying addresses. An address never displayed cannot be harvested. |
| Consent record | The Institute | Theirs to see, and the Institute's to evidence. |
| Terms they proposed | The Institute | Visible to them while awaiting review, and published only once approved. |

## Admitted collaborators

| Information | Who decides | Why this limit |
|---|---|---|
| First name | The Institute | A collaborator is known to the community by name. |
| Middle name | The Institute | Part of the name of record where a person gives one. |
| Family name | The Institute | A collaborator is known to the community by name. |
| Generational suffix | The Institute | Part of the name, held apart so sorting stays correct. |
| Preferred name | The Institute | Given in answer to how the Portal should address them. |
| Credentials after the name | The Institute sets the limit; the collaborator may show less | Chosen for display; the collaborator may show none. |
| Professional title | The Institute | The heart of a directory entry. |
| One-line summary | The Institute | Written for other collaborators to read. |
| About your work | The Institute sets the limit; the collaborator may show less | Written for the community; the collaborator may leave it blank. |
| Organization | The Institute | Professional affiliation is part of standing. |
| Current positions, up to three | The Institute | Senior people hold more than one appointment. |
| Fields | The Institute | The directory exists so that people can be found by these. |
| Specialties | The Institute | Optional precision, offered for discovery. |
| Links | The Institute sets the limit; the collaborator may show less | The collaborator chooses which to add and in what order. |
| Photograph | The Institute sets the limit; the collaborator may show less | The collaborator may choose not to publish one. |
| Languages | The Institute sets the limit; the collaborator may show less | Offered to help collaborators speak to one another. |
| Open to collaboration | The Institute sets the limit; the collaborator may show less | Entirely the collaborator's own decision. |
| Whether others may register an interest in working with them | The Institute sets the limit; the collaborator may show less | Theirs to switch off. Shown only as the presence or absence of the option. |
| Country | The Institute | Collected as part of the directory and searchable by it. |

## Anyone on the web

*Nothing. Deliberately.*

## What this guarantees

- A field nobody has declared belongs to the Institute alone, and the
  test suite refuses to pass until somebody states an intention. The
  Portal's mistakes err toward privacy.
- Every ceiling traces back to the promise made when the information was
  collected. A field is never later shown more widely than the person was
  told when they gave it.
- Enforcement happens in one place rather than page by page, so no future
  screen can get this wrong by forgetting.

