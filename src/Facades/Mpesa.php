<?php 

namespace Kabangi\Mpesa\Laravel\Mpesa\Facades;

use Illuminate\Support\Facades\Facade;

class Mpesa extends Facade {

	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor() {
        	return 'Kabangi\Mpesa\Laravel\Mpesa';	 
	}
}
