<?php


namespace Tests;


use Response\Config\Config;
use Response\Response;

class ResponseTest extends TestCase {


	public function testResponse() {
		$conf = Config::load( __DIR__ . '/../code.conf' );
//		var_dump( $conf['code'] );

		Response::setCodeConf( $conf['code'] );
//		Response::responseApi( 1, [ 'test' => 1 ], [] );
//		Response::responseApi( 1, [ 'test' => 1 ], [] ,'', 'jsonp');
//		Response::responseApi( 1, [ 'test' => 1 ], [] ,'', 'xml');
//		Response::responseApi(6000000, ['test' => 1], ['1', '2']);
//		Response::responseApi(6000000, ['test' => 1], ['1', '2'], '111');
//		Response::responseApi(1, ['test' => 1], [], '', 'xml');

//		var_dump(Response::responseApi(1,['test'=>1],[],'','json',0,0,'js',1));
//		var_dump(1);
	}


}