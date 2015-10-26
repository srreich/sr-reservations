This is a WordPress plugin for internal employees at a company to reserve a listed room for a period
of time to prevent conflicts.

Certain aspects of this plugin currently are not implemented (having an options page for things like
turning on email notifications without needing to make changes to the code and stronger reservation
cancellation procedures are two that come to mind). However, this plugin will currently allow users
to make and cancel reservations fairly easily.

If your WordPress installation is capable of sending out emails, feel free to edit sr_res_standard.php, 
sr_res_db.php, and sr_res_cal.php files, located in the model folder, at the "EMAIL RELATED CODE" labels
to allow sending email notifications whenever a user makes or cancels a reservation.