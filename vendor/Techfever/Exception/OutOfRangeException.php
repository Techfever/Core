<?php
namespace Techfever\Exception;

use Zend\Barcode\Exception;

/**
 * Exception for Zend\Barcode component.
 */
class OutOfRangeException extends Exception\OutOfRangeException implements ExceptionInterface {
}
