Error Handling
==============

_Reservations_ provides a flexible error handling mechanism:  
Errors caused by missing resources (such as reservation access) can be handled by a slot method. 
A default slot is already implemented and can be configured as follows:

```
plugin.tx_t3eventsreservation {
 <originControllerName> {
  <originActionName> {
   errorHandling = <method>|<arguments>
  }
 }
}
```
Where 
* `originControllerName` and `originActionName`  
  are the names of the controller action, where the error occured,
* `method`  
  is one of _redirect_, _forward_, _redirectToUri_, _redirectToListView_, _redirectToPage_, _pageNotFoundHandler_
* `arguments`  
  is a comma separated list of arguments for _method_

See default configuration in [Configuration/TypoScript/setup.txt](../Configuration/TypoScript/setup.txt).

## Examples 

### 1. Redirect 

(from default configuration)

```
plugin.tx_t3eventsreservation.reservation.new.errorHandling = redirect,edit
```
The setting above is interpreted as:
> When an error in _new_ action of _reservation_ controller occurs, _redirect_ the request to the _edit_ action

If the session has an a reservation reference, it will will be added as request argument. 

If the previous request hast an argument _reservation_, the default target controller is _Reservation_ and the argument will be passed. 

### 2. Redirect To Page

```
plugin.tx_t3eventsreservation.reservation.new.errorHandling = redirectToPage,5
```
The setting above is interpreted as:
> When an error in _new_ action of _reservation_ controller occurs, _redirect_ the request to page id _5_

### 3. Page Not Found Handler

```
plugin.tx_t3eventsreservation.reservation.new.errorHandling = pageNotFoundHandler
```
The setting above is interpreted as:
> When an error in _new_ action of _reservation_ controller occurs, use the _page not found handler_  configured for TYPO3


