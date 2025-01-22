# SOCS Pupil Calendar

## Usage instructions
1. Update config/pairs.php with the mis ID and socs keys for the pairs you want use. For now this has to be a manual job. But SOCS promise me that they will make an XML feed to get this data programatically.
2. Use the following code to generate the ICS feed for the pupil.
```php
$api = new MakeCalendar();
$api
  ->for("101356867549") // Where 101356867549 is the MIS ID
  ->make()
  ->asString();
  ```

Notes:
* Ensure that the MIS ID is a String. (some of them start with 0)
* Current default set up is that `filterByCategory` is set to only get Music Lessons. We can expand and refactor this.
