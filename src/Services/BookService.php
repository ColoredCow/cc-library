<?php
namespace coloredcow\books\Services;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Vision\Feature;
use Vision\Request\Image\LocalImage;
use Vision\Vision;

class BookService {

	public function __construct() {
	}

	public static function getBookInfo($file) {
		ini_set('max_execution_time', 3600);
		//return $this->getBookDetails(request()->input('isbn')); 
		//Will put in ENV
		$apiKey = "AIzaSyCM1QEosQzkoe8-HFBFN9xBOfPPCZBEfEk";

		$vision = new Vision(
			$apiKey, [ new Feature(Feature::TEXT_DETECTION, 100)]
		);
  
		$imagePath = $file->path();
		$response = $vision->request(new LocalImage($imagePath));
		$faces = $response->getTextAnnotations();
		$description = "";
		$lastOne = '';
		$data = [];
		$i = 0;
  
		foreach ($faces as $face) {
			$data[$i] = $face->getDescription();
			$i++;
			if('isbn' === strtolower($lastOne) || 'sbn' === strtolower($lastOne)) {
				$description = $face->getDescription();
			}
			$lastOne =  $face->getDescription();
		}
  
	   $description =  str_replace("-", "",  trim($description)); 
  
		if(strlen($description) < 13) {
  
		  return "try again with another image current isbn is :". $description;
		}
  
		if(!$description) {
		  return "invalid image please try again";
		}
  
		return self::getBookDetails($description); 
  
	  }
  
  
	  private static function getBookDetails($isbn) {
	  // $isbn = "9788172234980";
		$client = new Client();
		$res = $client->request('GET', "https://www.googleapis.com/books/v1/volumes?q=isbn:" . $isbn);
		$book = json_decode($res->getBody(), true);
		if(!isset($book['items'])) {
		  return "please try again";
		}
		return $book;
	  }
}
?>