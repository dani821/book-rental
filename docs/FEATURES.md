# Book Rental features guide

This guide walks through every feature that is built in Book Rental. Read it top to bottom. Each feature has a short explanation, then the screenshot that shows it.

## Authentication

### Login

The login screen is the entry point. A member or an admin signs in here with an email and password. The brand at the top reads "Book Rental".

![Login screen](screenshots/01-login.png)

### Login error handling

If the email or password is wrong, the app does not sign you in and shows the reason. Here the message reads "The provided credentials are incorrect."

![Login with wrong credentials](screenshots/02-login-error.png)

### Registration

A new visitor can create an account from the register screen. It asks for a name, email, password, and a password confirmation.

![Register screen](screenshots/03-register.png)

### Registration validation

The form checks the input before it creates the account. Here the password is too short (the rule is at least 8 characters), so the field shows the reason and the account is not created.

![Register with a short password](screenshots/04-register-validation.png)

---

## Navigation and roles

The app has a left sidebar. What it lists depends on who is signed in, so a member and an admin see different menus.

### Member dashboard

After a member signs in they land on the dashboard. The sidebar shows the member menus: Books and Rentals (plus Dashboard and Account, which everyone has).

![Member dashboard](screenshots/05-member-dashboard.png)

### Admin dashboard

An admin lands on the same dashboard layout, but the sidebar shows the admin menus instead: Users and Books. There is no Rentals menu for the admin.

![Admin dashboard](screenshots/06-admin-dashboard.png)

### Log out

The bottom of the sidebar shows the signed in user and a "Log out" button. Both members and admins use the same button. Clicking it ends the session, clears the saved token, and sends you back to the login screen.

![Log out button](screenshots/07-logout.png)

---

## Browse and search books (member)

### Books list and pagination

The Books page shows the catalogue as cards. The list is paginated at 15 books per page, so with 20 seeded books there are two pages. The footer says how many are shown and lets you move between pages.

![Books list with pagination](screenshots/10-books-list.png)

### Search by title or author

The search box filters the list as you type. The dropdown next to it picks whether you search by title or by author. Here a search for "Harry" narrows the list to one book.

![Search for Harry](screenshots/11-books-search.png)

### Filter by genre

The genre dropdown limits the list to one genre, for example Fantasy. It works together with the search box and the other filters.

![Filter by Fantasy genre](screenshots/12-books-filter-genre.png)

### Filter by availability

The "Available only" toggle hides books that have no free copies, so a member sees only what can be borrowed right now.

![Available only toggle on](screenshots/13-books-filter-availability.png)

### Sort

The sort dropdown changes the order of the list. The options include Title A to Z and Title Z to A, as well as author and year. The default is Title A to Z. This shot has Title Z to A selected.

![Sort by Title Z to A](screenshots/14-books-sort.png)

---

## Rent a book (member)

A member rents a book with the button on each card. The button has three states, and only one of them lets you rent. The rule behind the states is simple:

- A member can hold only **one active rental per book** at a time.
- Because of that rule, a book can show **Already rented** even when copies are still free for other members.
- **Not available** shows only when the book has zero free copies.

### Rent button: available and not yet rented

When the book has free copies and the member has not already rented it, the button is a normal "Rent" button.

![Enabled Rent button](screenshots/15-rent-enabled.png)

### Rent button: already rented

When the member already holds an active rental for this book, the button is disabled and reads "Already rented". This shows even though copies are still free for other members, which is the one rental per book rule in action. In this shot 1984 still has 4 of 5 copies free, but the member already has it.

![Already rented button](screenshots/16-rent-already-rented.png)

### Rent button: not available

When the book has zero free copies, the button is disabled and reads "Not available". The badge on the card also reads "Unavailable".

![Not available button](screenshots/17-rent-not-available.png)

### Rent success

Clicking an enabled "Rent" button borrows the book. The page shows a success message, the free copy count drops by one, and the same card flips to "Already rented" because the member now holds it.

![Rent success](screenshots/18-rent-success.png)

### The "already rented" or "no longer available" conflict

The API also blocks a second rental of the same book and a rental of a book that ran out, both with a 409 conflict. In the UI you cannot reach these by clicking, because the button is already disabled for those two cases.

---

## Book details (member)

### Book detail view

Clicking a book title opens its detail page. It repeats the genre and availability, the author, and a small table with the ISBN, published year, page count, and how many copies are free. A member can rent from here too.

![Book detail](screenshots/19-book-detail.png)

---

## Rentals (member)

### My rentals

The Rentals page lists the books the member currently holds. Each card shows reading progress, the due date, how many extensions are used, and buttons to log progress, extend the rental, or finish and return the book.

![Member rentals](screenshots/20-member-rentals.png)

### Log reading progress

A member can record how far they have read. The "Log progress" button opens a small dialog that asks for the current page. Saving it updates the progress bar on the card.

![Log reading progress dialog](screenshots/21-rental-log-progress.png)

### Extend a rental

The "Extend" button pushes the due date forward by two weeks. A rental can be extended up to two times. Here Cosmos moves to 1 of 2 extensions used and a later due date. After the limit the button reads "Max extensions" and is disabled.

![Rental extended](screenshots/22-rental-extend.png)

### Finish and return a rental

The "Finish" button returns the book. It asks for confirmation first. Finishing frees a copy for other members and moves the rental out of the active list.

![Finish rental confirm dialog](screenshots/23-rental-finish.png)

---

## Account (member)

A member can change their own password from the Account page. They must enter the current password first.

### Password form

This is the empty form. It asks for the current password, a new password, and a confirmation of the new password.

![Password form, empty](screenshots/50-account-password-empty.png)

### Wrong current password

If the current password is wrong, the change is rejected and the field shows the reason.

![Password update, wrong current password](screenshots/51-account-password-invalid.png)

### Password updated

When the current password is right and the new password is valid, the change goes through and the page confirms it.

![Password updated](screenshots/52-account-password-success.png)

---

## Manage books (admin)

An admin sees the same Books page as a member, but as a table with edit and delete actions, plus a "New book" button. This is the full create, read, update, delete set.

### Books table

The catalogue for an admin is a table. Each row links to the book and has edit and delete buttons on the right.

![Admin books table](screenshots/30-admin-books-table.png)

### Create: new book dialog

The "New book" button opens a dialog to add a title. It starts empty, with the genre set to Fiction and total copies set to 1.

![New book dialog](screenshots/31-admin-create-dialog.png)

### Create validation

The dialog checks the input before it saves. Submitting it empty shows the reasons next to each required field.

![New book dialog with validation errors](screenshots/32-admin-create-validation.png)

### Create success

With valid values the book is saved, the dialog closes, and the page confirms it. The new book then appears in the table. The shot uses a search to bring the new row into view, since it sorts onto a later page by title.

![Book created](screenshots/33-admin-create-success.png)

### Edit: prefilled dialog

The edit button opens the same dialog, but filled in with the current values for that book, ready to change.

![Edit book dialog](screenshots/34-admin-edit-dialog.png)

### Delete: confirm dialog

The delete button asks for confirmation first. It also reminds you that the rental history is kept and the book can be restored later.

![Delete confirm dialog](screenshots/35-admin-delete-confirm.png)

### Delete blocked

A book that has active rentals cannot be deleted. Confirming the delete returns a 409 conflict, and the dialog shows why. This is the conflict you can reach by clicking. In this shot "To Kill a Mockingbird" is still held by the member, so the delete is refused.

![Delete blocked by active rentals](screenshots/36-admin-delete-blocked.png)

---

## Users (admin)

### Users table

The Users page lists every account. Each row that is not your own has buttons to reset the password or delete the user. There is a "New user" button to add one. Your own row shows a "You" tag instead of actions.

![Admin users table](screenshots/40-admin-users.png)

### Add a new user

The "New user" button opens a dialog to create an account. It asks for a name, email, password, and a role of member or admin.

![New user dialog](screenshots/41-admin-user-create.png)

### Reset a user password

The key button on a user row opens a dialog to set a new password for that user. The admin does not need to know the user's old password.

![Reset password dialog](screenshots/42-admin-user-reset-password.png)

### Delete a user

The delete button on a user row asks for confirmation first. As with books, the user's rental history is kept and the account can be restored later.

![Delete user confirm dialog](screenshots/43-admin-user-delete.png)

---
