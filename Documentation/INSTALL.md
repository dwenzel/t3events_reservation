Installation
============

## Requirements
* PHP >=5.4
* TYPO3 CMS 6.2 - 8.x
* t3events > 0.29.0

## Installation

Command line
```bash
composer require typo3/cms 7.6.x
composer require cpsit/t3events_reservation
```

composer.json
```
{
  ...
  "require": {
    "typo3/cms": "^7.6",
    "cpsit/t3events_reservation": "^0.9.0"
  },
}
```

After installation via composer you have to activate it in the TYPO3 Extension Manager.

## Sources
The _Reservation_ extension is not yet available in the TYPO3 Extension Repository (TER). 
Please use the repository at  [Packagist](https://packagist.org/packages/cpsit/t3events_reservation) or
 [github](https://github.com/dwenzel/t3events_reservation).
