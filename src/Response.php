<?php

namespace Response;

use Response\Exceptions\ResponseException;

class Response {

	//code=>message map
	public static $codeConf;

	public static $type = [ 'json', 'jsonp', 'xml' ];

	public static function isCorrectJson( $body, $assoc = true ) {
		$content    = json_decode( $body, $assoc );
		$json_error = json_last_error();
		if ( $json_error == JSON_ERROR_NONE ) {
			if ( is_null( $content ) ) {
				return '';
			}

			return $content;
		}

		return false;
	}

	public static function jsonEncodeHold( $data, $isPretty = 0 ) {
		if ( $isPretty ) {
			$result = json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
		} else {
			$result = json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
		}

		$json_error = json_last_error();
		if ( $json_error !== JSON_ERROR_NONE ) {
			throw new ResponseException( "json encode error: " . json_last_error_msg() );

		}

		return $result;
	}

	public static function jsonDecodeHold( $data, $assoc = true ) {
		$result = self::isCorrectJson( $data, $assoc );

		if ( ! $result ) {
			throw new ResponseException( "json decode error: " . json_last_error_msg() );
		}

		return $result;
	}


	public static function xml_encode( $data, $encoding = 'utf-8', $root = 'Yundun' ) {
		$xml = '<?xml version="1.0" encoding="' . $encoding . '"?>';
		$xml .= '<' . $root . '>';
		$xml .= self::data_to_xml( $data );
		$xml .= '</' . $root . '>';

		return $xml;
	}


	public static function data_to_xml( $data ) {
		$xml = '';
		foreach ( $data as $key => $val ) {
			is_numeric( $key ) && $key = "item id=\"$key\"";
			$xml .= "<$key>";
			$xml .= ( is_array( $val ) || is_object( $val ) ) ? self::data_to_xml( $val ) : $val;
			list( $key, ) = explode( ' ', $key );
			$xml .= "</$key>";
		}

		return $xml;
	}


	public static function XML2Array( $xml, $recursive = false ) {
		if ( ! $recursive ) {
			$array = simplexml_load_string( $xml );
		} else {
			$array = $xml;
		}
		$newArray = array();
		$array    = ( array ) $array;
		foreach ( $array as $key => $value ) {
			$value = ( array ) $value;
			if ( isset ( $value [0] ) ) {
				$newArray [ $key ] = trim( $value [0] );
			} else {
				$newArray [ $key ] = self::XML2Array( $value, true );
			}
		}

		return $newArray;
	}

	public static function redirect307( $url, $replace = true, $exit = 1 ) {
		header( "Location: $url", $replace, 307 );
		$exit && exit();
	}

	public static function responseHttpCode( $httpCode = 200 ) {
		http_response_code( $httpCode );
	}


	public static function response( $data, $type = 'json', $isPrettyJson = 0, $exit = 1, $jsonpHeader = 'js' ) {
		$type = strtolower( $type );

		if ( ! in_array( $type, self::$type ) ) {
			$jsonStr = implode( "/", self::$type );
			throw new ResponseException( "response type $type not support , just support $jsonStr" );
		}

		switch ( $type ) {
			case 'json':
				if ( ! headers_sent() ) {
					header( 'Content-Type:application/json; charset=utf-8' );
				}
				echo( self::jsonEncodeHold( $data, $isPrettyJson ) );
				$exit && exit();
				break;
			case 'jsonp':
				if ( ! in_array( $jsonpHeader, [ 'js', 'json' ] ) ) {
					throw new ResponseException( "jsonpHeader just support js and json" );
				}
				if ( ! headers_sent() ) {
					switch ( $jsonpHeader ) {
						case 'js':
							header( 'Content-Type: application/javascript; charset=utf-8' );
							break;
						case 'json':
							header( 'Content-Type:application/json; charset=utf-8' );
							break;
					}
				}
				$handler = isset( $_GET['callback'] ) ? $_GET['callback'] : strtolower( 'callback' );
				echo( $handler . '(' . self::jsonEncodeHold( $data, $isPrettyJson ) . ');' );
				$exit && exit();
				break;
			case 'xml':
				if ( ! headers_sent() ) {
					header( 'Content-Type:text/xml; charset=utf-8' );
				}
				echo( self::xml_encode( $data ) );
				$exit && exit();
				break;
		}

		return true;
	}


	public static function setCodeConf( $conf ) {
		self::$codeConf = $conf;
	}


	public static function isSSOVersion2() {
		if ( isset( $_REQUEST['sso_version'] ) && $_REQUEST['sso_version'] == 2 ) {
			return true;
		}

		return false;
	}

	public static function responseApi( $code, $data, $codeParams = [], $message = '', $type = 'json', $isPrettyJson = 0, $exit = 1, $jsonpHeader = 'js', $returnResponse = 0 ) {
		if ( is_null( self::$codeConf ) ) {
			throw new ResponseException( "please set code conf" );
		}

		if ( ! isset( self::$codeConf[ $code ] ) ) {
			throw new ResponseException( "code $code not exists, please conf it" );
		}
		$response = [
			'status' => [
				'code'      => $code,
				'message'   => $message,
				'create_at' => date( 'Y-m-d H:i:s' )
			],
			'data'   => $data
		];
		if ( ! isset( $response['status']['message'] ) || empty( $response['status']['message'] ) ) {
			$response['status']['message'] = vsprintf( $codeParams ? self::$codeConf[ $code ] : ( str_replace( [
				'%s',
				'%d'
			], '', self::$codeConf[ $code ] ) ), ( $codeParams ? array_values( $codeParams ) : [] ) );
		}

		if ( $returnResponse ) {
			return $response;
		}

		self::response( $response, $type, $isPrettyJson, $exit, $jsonpHeader );
	}


}