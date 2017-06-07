<?php
class Bitly{
	//usuario: ferminako
	//pass : Navarra1221/

	static function make_bitly_url($url,$login,$appkey,$format = 'xml',$version = '2.0.1')
	{
		//create the URL
		$bitly = 'http://api.bit.ly/shorten?version='.$version.'&longUrl='.urlencode($url).'&login='.$login.'&apiKey='.$appkey.'&format='.$format;

		//get the url
		//could also use cURL here
		$response = file_get_contents($bitly);

		//parse depending on desired format
		if(strtolower($format) == 'json')
		{
			$json = @json_decode($response,true);
			return $json['results'][$url]['shortUrl'];
		}
		else //xml
		{
			$xml = simplexml_load_string($response);
			return 'http://bit.ly/'.$xml->results->nodeKeyVal->hash;
		}
	}

	static function acortarUrl($url){
		return self::make_bitly_url($url,'ferminako','R_ed70c6077cc642339d2104013ec32d18','json');
	}


	// /* usage */
	// $short = make_bitly_url('https://davidwalsh.name','davidwalshblog','R_96acc320c5c423e4f5192e006ff24980','json');
	// echo 'The short URL is:  '.$short;

}
?>