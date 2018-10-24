There are Lumen Events to add Listener to. You can add Plugins via this Listeners without change the native Code

## UserRegisterEvent
Fired if a new user registertd. Payload is the User Object.

__System-Usage:__

* Send E-Mail to validate the Users E-Mail adress

## UserLoggedInEvent
Fired if a user successful logged in. Payload is the User Object.

__System-Usage__

* Save Logins to DB

## OrganisationCreate
Fired if a new Organisation is created. Payload is a Organisation Object.

__System-Usage__

* _none_

## OrganisationUpdated
Fired if a Organisation is updated. PAyload is the new Organisation Object and a array with changes parameters

__System-Usage__

* _Notification for Users_
* Log Changes to DB

## ItemUpdated
Fired if a Item in a Organisation has beedn updated. Payload is the Item which was updated and a array with the changes.

__System-Usage__

* Log Changes to DB