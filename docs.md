- Can be used standalone or within a silex project like in AnyContent Backend. See web/index.php for an example.
- Will not allow to overwrite protected values
- but will not check for mandatory and unique properties, should be handled within client.

# error codes

const ERROR_400_BAD_REQUEST = 1;
const ERROR_400_UNKNOWN_PROPERTIES = 8;

const ERROR_404_UNKNOWN_REPOSITORY = 2;
const ERROR_404_UNKNOWN_CONTENTTYPE = 3;
const ERROR_404_RECORD_NOT_FOUND = 4;
const ERROR_404_UNKNOWN_CONFIGTYPE = 5;
const ERROR_404_CONFIG_NOT_FOUND = 6;
const ERROR_404_FILE_NOT_FOUND = 7;

const ERROR_404_UNKNOWN_WORKSPACE = 20;
const ERROR_404_UNKNOWN_LANGUAGE = 21;
const ERROR_404_UNKNOWN_VIEW = 22;
