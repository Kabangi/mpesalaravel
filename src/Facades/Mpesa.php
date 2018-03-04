<?php 

namespace Kabangi\MpesaLaravel\Facades;

use Illuminate\Support\Facades\Facade;

class Mpesa extends Facade {
	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor() {
        	return 'Kabangi\MpesaLaravel\Mpesa';	 
	}
}
