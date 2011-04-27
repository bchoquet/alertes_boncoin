<?php
require_once 'simplehtmldom/simple_html_dom.php';
require_once 'Annonce.php';
class AlertesBoncoin{

	private $title;

	private $dest;

	private $listURL;

	private $memDir;

	private $ads = array();

	private $new = array();


	public function __construct($listURL, $dest, $title, $memDir='mem'){
		$this->dest = $dest;
		$this->listURL = $listURL;
		$this->title =  $title;
		$this->memDir = $memDir;
		if(!file_exists($this->memDir)){
			mkdir($this->memDir);
		}
	}

	public function run(){
		$this->loadAds();
		$page = $this->scrapePage($this->listURL);
		if($page){
			$ads = $this->scrapeListPage($page);
			foreach ($ads as $ad){
				if(!in_array($ad, $this->ads)){
					$this->ads[] = $ad;
					$this->new[] = $this->getAdDetails($ad);
				}
			}
		}

		//print count($this->new).chr(10);

		if( count($this->new)){
			$this->sendMail();
			$this->saveAds();
		}
	}

	/**
	 * Charge la liste des annonces déjà lues
	 */
	private function loadAds(){
		$memFile = $this->getMemFileName();
		if(file_exists($memFile)){
			$contents = file_get_contents($memFile);
			$this->ads = explode(chr(10), $contents);
		}
	}

	/**
	 * Sauvegarde la liste des annonces déjà lues
	 */
	private function saveAds(){
		$memFile = $this->getMemFileName();
		file_put_contents($memFile, implode(chr(10), $this->ads));
	}

	/**
	 * Renvoie le chemin du fichier mémorisant la liste des annonces déjà lues
	 */
	private function getMemFileName(){
		return $this->memDir . '/' . md5($this->listURL);
	}

	/**
	 * Charge le contenu d'une URL
	 * @param string $page URL de la page
	 * @return string code HTML de la page
	 */
	private function scrapePage($page){
	    print $page.chr(10);
		// Création d'une nouvelle ressource cURL
		$ch = curl_init();

		// Configuration de l'URL et d'autres options
		curl_setopt($ch, CURLOPT_URL, $this->listURL);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		// Récupération de l'URL et affichage sur le naviguateur
		$data = curl_exec($ch);

		if(!$data){
			print curl_error($ch);
		}

		// Fermeture de la session cURL
		curl_close($ch);
		file_put_contents('mem/test_'.md5($page).'.html', $data);
		return $data;
	}

	/**
	 * Extrait la liste des annonces depuis une page de recherche du bon coin
	 * @param string $html code HTML des résultats de recherche
	 * @return array	liste des annonces
	 */
	private function scrapeListPage($html){
		$ads = array();

		$dom = new simple_html_dom();
		$dom->load($html);
		foreach($dom->find('#hl td[nowrap] a') as $data)
		{
		    $url = $data->getAttribute('href');
		    $ads[] = $url;
		}

		return $ads;

	}

	/**
	 * Retourne le détail d'une annonce
	 */
	private function getAdDetails($ad){
		$html = $this->scrapePage($ad);
		$annonce = new Annonce($ad);

		$domPage = new simple_html_dom();
	    $domPage->load($html);

	    $annonce->titre = html_entity_decode($domPage->find('h1', 0)->plaintext);

	    $descr = $domPage->find('table.AdviewContent span.lbcAd_text', 0);
	    if($descr) $annonce->description = html_entity_decode($descr->plaintext);

	    foreach($domPage->find('.lbcAdParams .ad_details') as $detail){

	    	if(	$label = $detail->find('label', 0)){
	    		$name = $label->plaintext;
	    	}
	    	if($strong = $detail->find('strong', 0)){
	        	$val = $strong->plaintext;
	    	}
	    	if( $name && $val ){
	        	$annonce->addProperty(html_entity_decode($name),  html_entity_decode($val));
	    	}
	    }
	    //print $annonce;
	    return $annonce;
	}

	/**
	 * Envoie le mail d'alerte contenant les nouvelles annonces
	 */
	private function sendMail(){
		$headers = array(
			'From: '.$this->dest,
			'Reply-To: '.$this->dest,
			'Content-Type: text/plain; charset=utf-8',
		);

		$subject = $this->title.' : '.count($this->new).' nouvelle(s) annonce(s)';

		$body = implode(chr(10), $this->new);

		mail($this->dest, $subject, $body, implode(chr(10), $headers));
	}
}