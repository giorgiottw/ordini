<?php
ini_set('display_errors', 'on'); 
ini_set('error_reporting', E_ALL);


// Here we define constants /!\ You need to replace this parameters
define('DEBUG', false);						// Debug mode

define('PS_SHOP_PATH', 'http://www.gipys.it/shop');
define('PS_WS_AUTH_KEY', '7T88ZDH2577LZEJLVDFKEXQRIG2ZP17T');	// Auth key (Get it in your Back Office)

include('./PSWebServiceLibrary.php');

class PrestashopWSH {
	
	private $_url, $_psk, $_debug, $webService;
		
	function __construct($url, $psk, $debug=false) {
		
		$this->_url = $url;
		$this->_psk = $psk;
		$this->_debug =$debug;
		$this->webService = new PrestaShopWebservice($this->_url, $this->_psk, $this->_debug);
	}
	
	
	/*
	public function getProductByReference($reference,$json=false) {
		
		try {
			$opt = array('resource'=>'products',     
				     'filter[reference]' => "".$reference."",
				     );
			$xml = $this->webService->get($opt);
			
			if(count($xml->products->children())) {
				return $this->getProductById($xml->products->product->attributes()->id*1,$json);
			}
			else return array("status"=>0, "message"=>"Product '".$reference."' not found."); 
		}
		catch (PrestaShopWebserviceException $e) { $this->exceptionManager($e); }
	}
	*/
	
	/*
	public function getProductById($id,$json=false) {
		try {
			$opt = array('resource'=>'products', 'id'=>$id,);
			if($json) {
				
				$res=$this->webService->get($opt);
				return $this->xml2array($res->asXML());
			}
			else return $this->webService->get($opt);
		}
		catch (PrestaShopWebserviceException $e) { $this->exceptionManager($e); }
	}
	*/
	
	
	
	/*
	private function getReferenceById($id) {
		try {
			$opt = array('resource'=>'products', 'id'=>$id,);
			$xml = $this->webService->get($opt);
			$resources = $xml->children()->children();
			return "".$resources->reference;
		}
		catch (PrestaShopWebserviceException $e) { $this->exceptionManager($e); }
	}
	*/
	
	
	/*
	public function getProductQty($q, $search_by='reference')
	{
		try {
			if($search_by === 'reference') $xml=$this->getProductByReference($q);
			if(isset($xml['status']) && !$xml['status']) { 	return $xml; }
			
			$resources = $xml->product->children()->associations->children()->stock_availables->children()->stock_available->children();
			$opt = array('resource'=>'stock_availables', 'id'=>$resources->id);
			$xml = $this->webService->get($opt);
			$resources = $xml->stock_available->children();
			return array('getproductqtyreply'=>array('quantity'=>$resources->quantity."", 'q'=>$q, 'searchby'=>$search_by));
		}
		catch (PrestaShopWebserviceException $e) { $this->exceptionManager($e); }
	}
	*/
	
	
	/*
	public function setProductQty($q,$qty, $search_by='reference') {
		
		try
		{
			if($search_by === 'reference') $xml=$this->getProductByReference($q);
			elseif($search_by === 'id') $xml= $this->getProductById($q);
			
			if(isset($xml['status']) && !$xml['status']) { 	return array('productqtyreply'=>$xml); }
			
			$resources = $xml->product->children()->associations->children()->stock_availables->children()->stock_available->children();
			$opt = array('resource'=>'stock_availables', 'id'=>$resources->id);
			$xml = $this->webService->get($opt);
			$resources = $xml->stock_available->children();
			
			$resources->quantity=$qty;
			
			$opt['putXml'] = $xml->asXML();
			$xml = $this->webService->edit($opt);
			return array('productqtyreply'=>array("status"=>1, "message"=>"Successfully updated.")); 
		}
		
		catch (PrestaShopWebserviceException $e) { $this->exceptionManager($e); }
		
	}
	*/
	
	/*
	public function addProduct($data) {
		
		try {
			$opt = array('resource'=>'products',     
				     'filter[reference]' => "".$data['reference']."",
				     );
			$xml = $this->webService->get($opt);
			if(count($xml->children()->children())>0)
			{
				return array('productreply'=>array("status"=>0, "message"=>"Duplicated reference.", "id"=>0));
				exit();
			}
			
			
			$id_category=$this->getCategoryByName($data['category']);
			$id_supplier=$this->getSupplierByName($data['supplier']);
			
			$xml = $this->webService -> get(array('url' => $this->_url . 'api/products?schema=blank'));
			$resources = $xml -> children() -> children();
			
			// ELIMINO I CAMPI NON REQUIRED 
			unset($resources->id);
			unset($resources->id_shop_default);
			unset($resources->date_add);
			unset($resources->date_upd);
			unset($resources->associations->combinations);
			unset($resources->associations->product_options_values);
			unset($resources->associations->product_features);
			unset($resources->associations->tags);
			unset($resources->associations->stock_availables->stock_available->id_product_attribute);
			
			// PRICE 
			$resources->price=$data['price'];
			$resources->id_tax_rules_group=$data['id_tax_rules_group'];
			$resources->wholesale_price=$data['wholesale_price'];
			$resources->active=$data['active'];
			$resources->ean13=$data['ean13'];
			$resources->reference=$data['reference'];
			$resources->unity=$data['unity'];
			
			// CATEGORY
			$resources->associations->categories->category->id=$id_category->id*1;
			$resources->id_category_default=$id_category*1;
			
			// ID SUPPLIER
			$resources->id_supplier=$id_supplier;
			$resources->id_default_combination="0";
			$resources->id_manufacturer=$data['id_manufacturer'];
			
			// PRODUCT NAME 
			$node = dom_import_simplexml($resources -> name -> language[0][0]);
			$no = $node -> ownerDocument;
			$node -> appendChild($no -> createCDATASection($data['name']));
			$resources -> name -> language[0][0] = $data['name'];
			$resources -> name -> language[0][0]['id'] = $data['id_language'];
			$resources -> name -> language[0][0]['xlink:href'] = $this->_url . 'api/languages/'.$data['id_language'];
			
			$resources->available_for_order=$data['available_for_order'];
			
			// DESCRIPTION 
			$node = dom_import_simplexml($resources -> description -> language[0][0]);
			$no = $node -> ownerDocument;
			$node -> appendChild($no -> createCDATASection($data['description']));
			$resources -> description -> language[0][0] = $data['description'];
			$resources -> description -> language[0][0]['id'] = $data['id_language'];
			$resources -> description -> language[0][0]['xlink:href'] = $this->_url . 'api/languages/'.$data['id_language'];
			
			
			// DESCRIPTION SHORT 
			$node = dom_import_simplexml($resources -> description_short -> language[0][0]);
			$no = $node -> ownerDocument;
			$node -> appendChild($no -> createCDATASection($data['description_short']));
			$resources -> description_short -> language[0][0] = $data['description_short'];
			$resources -> description_short -> language[0][0]['id'] = $data['id_language'];
			$resources -> description_short -> language[0][0]['xlink:href'] = $this->_url . 'api/languages/'.$data['id_language'];
			
			
			// LINK_REWRITE 
			$node = dom_import_simplexml($resources -> link_rewrite -> language[0][0]);
			$no = $node -> ownerDocument;
			$node -> appendChild($no -> createCDATASection($data['link_rewrite']));
			$resources -> link_rewrite -> language[0][0] = $data['link_rewrite'];
			$resources -> link_rewrite -> language[0][0]['id'] = $data['id_language'];
			$resources -> link_rewrite -> language[0][0]['xlink:href'] = $this->_url . 'api/languages/'.$data['id_language'];
			
			
			// META TITLE 
			$node = dom_import_simplexml($resources -> meta_title -> language[0][0]);
			$no = $node -> ownerDocument;
			$node -> appendChild($no -> createCDATASection($data['meta_title']));
			$resources -> meta_title -> language[0][0] = $data['meta_title'];
			$resources -> meta_title -> language[0][0]['id'] = $data['id_language'];
			$resources -> meta_title -> language[0][0]['xlink:href'] = $this->_url . 'api/languages/'.$data['id_language'];
			
			// META DESCRIPTION 
			$node = dom_import_simplexml($resources -> meta_description -> language[0][0]);
			$no = $node -> ownerDocument;
			$node -> appendChild($no -> createCDATASection($data['meta_description']));
			$resources -> meta_description -> language[0][0] = $data['meta_description'];
			$resources -> meta_description -> language[0][0]['id'] = $data['id_language'];
			$resources -> meta_description -> language[0][0]['xlink:href'] = $this->_url . 'api/languages/'.$data['id_language'];
			
			// META KEYWORDS 
			$node = dom_import_simplexml($resources -> meta_keywords -> language[0][0]);
			$no = $node -> ownerDocument;
			$node -> appendChild($no -> createCDATASection($data['meta_keywords']));
			$resources -> meta_keywords -> language[0][0] = $data['meta_keywords'];
			$resources -> meta_keywords -> language[0][0]['id'] = $data['id_language'];
			$resources -> meta_keywords -> language[0][0]['xlink:href'] = $this->_url . 'api/languages/'.$data['id_language'];
			
			
			$opt = array('resource' => 'products');
			$opt['postXml'] = $xml->asXML();
			
			$xml = $this->webService->add($opt);
			$res=$xml->children()->children();
			$generated_id=$res->id*1;
			
			
			//AGGIUNGO DIPENDENZA QUANTITA'
			
			if(isset($data['quantity']) && $data['quantity']>0) $this->setProductQty($generated_id,$data['quantity'], 'id');
			
			//IMPOSTO IL PREZZO PER IL GRUPPO SPECIFICO
			if(isset($data['specific_price']) && is_array($data['specific_price']))
			{
				$res_group = $this->getGrouByName($data['specific_price']);
				if($res_group)
				{
					$data_sp=array('id_product'=>$generated_id,
						       'price'=>$data['price'],
						       'id_group'=>"".$res_group->attributes()->id,
						       'reduction'=>$data['specific_price']['reduction'],
						       'reduction_type'=>$data['specific_price']['reduction_type'],
						       );
					
					$this->setSpecificPrices($data_sp);
				}
			}
			
			return array('productreply'=>array("status"=>1, "message"=>$data['reference']." successfully added.", "id"=>$generated_id)); 
		}
		
		catch (PrestaShopWebserviceException $e) { echo $this->exceptionManager($e); }
	}
	*/
	
	
	/*
	public function delProductByReference($reference) {
		try {
			
			$opt = array('resource'=>'products',     
				     'filter[reference]' => "".$reference."",
				     );
			$xml = $this->webService->get($opt);
			if(count($xml->children()->children())>0) {
				
				$opt = array('resource'=>'products',     
				     'id' => "".$xml->products->product->attributes()->id*1,
				     );
								
				$xml = $this->webService->delete($opt);
				return array("status"=>1, "message"=>"Product ".$reference." deleted successfully.");
			}
			else {
				return array("status"=>0, "message"=>"Product ".$reference." not found.");
				exit();
			}
			
		}
		catch (PrestaShopWebserviceException $e) { $this->exceptionManager($e); } 
		
	}
	*/
	
	/*
	public function setSpecificPrices($data) {
		
		try {
			$opt = array('resource'=>'specific_prices',
				      'filter[id_product]' => $data['id_product'],
				    );
			$xml= $this->webService->get($opt);
			$id_specific_price=0;
			
			if(count($xml->children()->children())>0) {
				
				$resources=$xml->children()->children();
				$id_specific_price=$resources->attributes()->id*1;
				
				$opt = array('resource'=>'specific_prices',
				      'id' => $id_specific_price,
				    );
				$xml= $this->webService->get($opt);
				$resources= $xml->children()->children();
				
				$resources->price=$data['price'];
				$resources->id_group=$data['id_group'];
				$resources->reduction=$data['reduction'];
				$resources->reduction_type=$data['reduction_type'];
				
				$opt['putXml'] = $xml->asXML();
				$xml = $this->webService->edit($opt);
			}
			else
			{
				
				$xml = $this->webService -> get(array('url' => $this->_url . 'api/specific_prices?schema=blank'));
				
				$resources = $xml -> children() -> children();
				
				unset($resources->id);
				unset($resources->id_shop_group);
				
				$resources->id_shop=0;
				$resources->id_cart=0;
				$resources->id_product=$data['id_product'];
				unset($resources->id_product_attribute);
				
				$resources->id_currency=0;
				$resources->id_country=0;
				$resources->id_group=$data['id_group'];
				$resources->id_customer=0;
				unset($resources->id_specific_price_rule);
				
				$resources->price=$data['price'];
				$resources->from_quantity=0;
				$resources->reduction=$data['reduction'];
				$resources->reduction_type=$data['reduction_type'];
				
				$resources->from="0000-00-00 00:00:00";
				$resources->to="0000-00-00 00:00:00";
				
				$opt = array('resource'=>'specific_prices', );
				$opt['postXml'] = $xml->asXML();
				$xml = $this->webService->add($opt);
			}
		}
		catch (PrestaShopWebserviceException $e) { $this->exceptionManager($e); } 
	}
	*/
	
	
	/*
	public function getGrouByName($data) {
		try {
			$opt = array('resource'=>'groups',
				     'filter[name]' => $data['group']
			);
			$xml= $this->webService->get($opt);
			
			if(count($xml->children()->children()>0)) {
				$resources = $xml->children()->children();
				return $resources;
			}
			else return null;
		}
		catch (PrestaShopWebserviceException $e) { $this->exceptionManager($e); }
		
	}
	*/
	
	
	/*
	public function getCategoryByName($data, $json=false) {
		
		try {
			$opt = array('resource'=>'categories',
				     'filter[name]' => $data['name']
			);
			$xml= $this->webService->get($opt);
			if(count($xml->children()->children())>0) {
				
				$resources = $xml->children()->children();
				$id="".$resources->attributes()->id;
				return $this->getCategoryById($id,$json);
			}
			else
			{
				if(!$json) return $this->addCategory($data,$json);
				else
				{
					return array("status"=>0, "message"=>"Category not found", );
					exit();
				}
			}
		}
		catch (PrestaShopWebserviceException $e) { $this->exceptionManager($e); }
	}
	*/
	
	/*
	public function getCategoryById($id_category,$json=false) {
		try {
			
			$opt = array('resource'=>'categories',
				     'id' => $id_category
			);
				
			$xml= $this->webService->get($opt);
			$resources = $xml->children()->children();
			
			if($json) return $this->xml2array($xml->asXml());
			else return $resources;
			
		}
		catch (PrestaShopWebserviceException $e) { $this->exceptionManager($e); }
	}
	*/
	
	/*
	public function delCategory($data) {
		
		
		try {
			
			$opt = array('resource'=>'categories');
			$xml= $this->webService->get($opt);
			$resources = $xml->children()->children();
		}
		catch (PrestaShopWebserviceException $e) { $this->exceptionManager($e); }
		
	}
	*/
	
	/*
	public function addCategory($data,$json=false) {
		
		try {
			
			$opt = array('resource'=>'categories',
				     'filter[name]' => $data['name']
			);
			$xml= $this->webService->get($opt);
			if(count($xml->children()->children())>0) 
			{
				return array("status"=>0, "message"=>"Category already present.", );
				exit();
			}
			else {
			
				$xml = $this->webService -> get(array('url' => $this->_url . 'api/categories?schema=blank'));
				$resources = $xml -> children() -> children();
				unset($resources->id);
				
				$resources->active=true;
				
				$resources->id_parent=$data['id_home_category'];
				
				// NODE NAME 
				$node = dom_import_simplexml($resources -> name -> language[0][0]);
				$no = $node -> ownerDocument;
				$node -> appendChild($no -> createCDATASection($data['name']));
				$resources -> name -> language[0][0] = $data['name'];
				$resources -> name -> language[0][0]['id'] = $data['id_language'];
				$resources -> name -> language[0][0]['xlink:href'] = $this->_url . 'api/languages/'.$data['id_language'];
				
				// NODE LINK REWRITE 
				$node = dom_import_simplexml($resources -> link_rewrite -> language[0][0]);
				$no = $node -> ownerDocument;
				$node -> appendChild($no -> createCDATASection($data['link_rewrite']));
				$resources -> link_rewrite -> language[0][0] = $data['link_rewrite'];
				$resources -> link_rewrite -> language[0][0]['id'] = $data['id_language'];
				$resources -> link_rewrite -> language[0][0]['xlink:href'] = $this->_url . 'api/languages/'.$data['id_language'];
				
				$opt = array('resource' => 'categories');
				$opt['postXml'] = $xml->asXML();
				$xml = $this->webService->add($opt);
				
				$resources=$xml->children()->children();
				
				if($json) return $this->xml2array($xml->asXml());
				else return $resources;
			}
		}
		catch (PrestaShopWebserviceException $e) { $this->exceptionManager($e); }
	}
	*/
	
	
	/*
	public function updateCategory($data) {
		
		try {
			
			$opt = array('resource'=>'categories');
			$xml= $this->webService->get($opt);
			$resources = $xml->children()->children();
			
		}
		catch (PrestaShopWebserviceException $e) { $this->exceptionManager($e); }
		
	}
	*/
	
	
	/*
	public function getManufacturerByName($data, $json=false) {
		
		try {
			$opt = array('resource'=>'manufacturers',
				     'filter[name]' => $data['name']
			);
			$xml= $this->webService->get($opt);
			if(count($xml->children()->children())>0) {
				
				
				$resources = $xml->children()->children();
				$id="".$resources->attributes()->id;
				return $this->getManufacturerById($id,$json);
			}
			else
			{
				return $this->addManufacturer($data, $json);
			}
		}
		catch (PrestaShopWebserviceException $e) { $this->exceptionManager($e); }
		
	}
	*/
	
	/*
	public function getManufacturerById($id_manufacturer,$json=false) {
		try {
			$opt = array('resource'=>'manufacturers',
				     'id' => $id_manufacturer
			);
			
			$xml= $this->webService->get($opt);
			$resources = $xml->children()->children();
			if($json) return $this->xml2array($xml->asXml());
			else return $resources;
		}
		catch (PrestaShopWebserviceException $e) { $this->exceptionManager($e); }
	}
	*/
	
	/*
	public function addManufacturer($data,$json=false) {
		
		try {
			$xml = $this->webService -> get(array('url' => $this->_url . 'api/manufacturers?schema=blank'));
			$resources = $xml -> children() -> children();
			
			unset($resources->id);
			$resources->active=true;
			$resources->name=$data['name'];
			unset($resources->date_add);
			unset($resources->date_upd);
			unset($resources->description);
			unset($resources->short_description);
			unset($resources->meta_title);
			unset($resources->meta_description);
			unset($resources->meta_keywords);
			unset($resources->associations);
			unset($resources->link_rewrite);
			
			$opt = array('resource' => 'manufacturers');
			$opt['postXml'] = $xml->asXML();
			$xml = $this->webService->add($opt);
			$res=$xml->children()->children();
			
			$generated_id=$res->id*1;
			
			if($json) return $this->xml2array($xml->asXml());
			else return $generated_id;
		}
		catch (PrestaShopWebserviceException $e) { $this->exceptionManager($e); }
	}
	*/
	
	/*
	public function getOrdersByStatus($status) {
		
		try {
			
			$opt = array('resource'=>'orders',
				      'filter[current_state]' => $status,
				     );
			$xml= $this->webService->get($opt);
			
			$orders_array=array();
			
			foreach($xml->orders->order as $o) {
				$orders_array["".$o->attributes()->id]['order_info']=array();
			}
			if(count($orders_array))
			{
				return $this->getOrdersByIds($orders_array);
			}
		}
		catch (PrestaShopWebserviceException $e) { $this->exceptionManager($e); } 
	}
	*/
	
	/*
	public function setOrderStatus($id,$new_status) {
		try {
			$opt = array('resource'=>'orders',
				      'id' => $id,
				    );
			
			$xml= $this->webService->get($opt);
			$resources = $xml->children()->children();
			$resources->current_state=$new_status;
			
			$opt['putXml'] = $xml->asXML();
			$xml = $this->webService->edit($opt);
			return array('orderstatusreply'=>array("status"=>1, "message"=>"Order status successfully updated.")); 
		}
		catch (PrestaShopWebserviceException $e) { $this->exceptionManager($e); }
	}
	*/
	
	
	public function getOrdersByIds($ids) {
		print_r($ids);
		try
		{
			$orders['order']=array();
			
			foreach($ids as $key => $data)
			{
				$opt_order = array('resource'=>'order_details', 'filter[id]' => $data);
				$opt = array('resource'=>'order_details', 'filter[id_order]' => $data);
				$xml= $this->webService->get($opt);
				$ris = $xml;
				print_r($ris);
				$resources = $xml->children()->children();
				
				$products=array();
				foreach($resources as $product){
				
					
				
					$opt = array('resource'=>'order_details', 'id' => $product->attributes()->id);
					$xml= $this->webService->get($opt);
					$resources2 = $xml->children();
					foreach($resources2 as $prod){
						// creao array prodotto singolo
						
						array_push($products, array(
							"product_reference"=>$prod->product_reference,
							"product_name"=>"".$prod->product_name,
							"product_quantity"=>"".$prod->product_quantity,
							"product_price"=>"".$prod->product_price,
							"unit_price_tax_incl"=>"".$prod->unit_price_tax_incl, 
							"unit_price_tax_excl"=>"".$prod->unit_price_tax_excl,
						));
						
						
					}
					
				}
				
				$order_info=array(
					'id'=>"".$ris->id,
					'reference'=>"".$ris->reference,
					'date_add'=>"".$ris->date_add,
					'date_upd'=>"".$ris->date_upd,
					
					'total_paid'=>"".$ris->total_paid,
					'total_paid_tax_incl'=>"".$ris->total_paid_tax_incl,
					'total_paid_tax_excl'=>"".$resources->total_paid_tax_excl,
					'total_paid_real'=>"".$resources->total_paid_real,
					
					'total_products'=>"".$resources->total_products,
					'total_products_wt'=>"".$resources->total_products_wt,
					
					'total_shipping'=>"".$resources->total_shipping,
					'total_shipping_tax_incl'=>"".$resources->total_shipping_tax_incl,
					'total_shipping_tax_excl'=>"".$resources->total_shipping_tax_excl,
					'carrier_tax_rate'=>"".$resources->carrier_tax_rate,
					
					'total_wrapping'=>"".$resources->total_wrapping,
					'total_wrapping_tax_incl'=>"".$resources->total_wrapping_tax_incl,
					'total_wrapping_tax_excl'=>"".$resources->total_wrapping_tax_excl,
					
					'shipping_number'=>"".$resources->shipping_number,
					'conversion_rate'=>"".$resources->conversion_rate,
					
					
					'delivery_informations'=>$this->getAddresses("".$resources->id_address_delivery),
					'payment'=>"".$resources->payment,
					'order_status'=>$this->getOrderStateById("".$resources->current_state),	
					'products'=>$products,
					);
				
				array_push($orders['order'],$order_info);
				
			}
			return $orders;
		}
		catch (PrestaShopWebserviceException $e) { $this->exceptionManager($e); }
	}
	
	
	private function getAddresses($id) {
		
		try {
			$opt = array('resource'=>'addresses', 'id'=>$id);
			$xml= $this->webService->get($opt);
			$resources = $xml->children()->children();
			return array('alias'=>"".$resources->alias,
				     'firstname'=>"".$resources->firstname,
				     'lastname'=>"".$resources->lastname,
				     'address1'=>"".$resources->address1,
				     'address2'=>"".$resources->address2,
				     'postcode'=>"".$resources->postcode,
				     'city'=>"".$resources->city,
				     'phone_mobile'=>"".$resources->phone_mobile,
				     
				    );
		}
		catch (PrestaShopWebserviceException $e) { $this->exceptionManager($e); }
	}
	
	
	private function getOrderStateById($id) {
		
		try {
			$opt = array('resource'=>'order_states', 'id'=>$id);
			$xml= $this->webService->get($opt);
			$resources = $xml->children()->children();
			return array('name'=>"".$resources->name->language,
				);
		}
		catch (PrestaShopWebserviceException $e) { $this->exceptionManager($e); }
	}
	


	
	public function getSupplierByName($data, $json=false) {
		
		try {
			$opt = array('resource'=>'suppliers',
				     'filter[name]' => $data['name']
			);
			
			$xml= $this->webService->get($opt);
			if(count($xml->children()->children())>0) {
				
				$resources = $xml->children()->children();
				$id="".$resources->attributes()->id;
				
				
				return $this->getSupplierById($id,$json);
			}
			else
			{
				return $this->addSupplier($data,$json);
			}
		}
		catch (PrestaShopWebserviceException $e) { $this->exceptionManager($e); }
		
	}
	
	public function getSupplierById($id_supplier,$json=false) {
		try {
			$opt = array('resource'=>'suppliers',
				     'id' => $id_supplier
			);
			$xml= $this->webService->get($opt);
			$resources = $xml->children()->children();
			
			if($json) return $this->xml2array($xml->asXml());
			else return $resources;
		}
		catch (PrestaShopWebserviceException $e) { $this->exceptionManager($e); }
	}
	
	public function addSupplier($data, $json=false) {
		
		try {
			$xml = $this->webService -> get(array('url' => $this->_url . 'api/suppliers?schema=blank'));
			$resources = $xml -> children() -> children();
			
			unset($resources->id);
			$resources->name=$data['name'];
			
			unset($resources->link_rewrite);
			
			$opt = array('resource' => 'suppliers');
			$opt['postXml'] = $xml->asXML();
			$xml = $this->webService->add($opt);
			$res=$xml->children()->children();
			
			$generated_id=$res->id*1;
			if($json) return $this->xml2array($xml->asXML());
			else return $res;
		}
		catch (PrestaShopWebserviceException $e) { $this->exceptionManager($e); }
	}
	
	public function exceptionManager($e) {
		$trace = $e->getTrace();
		if ($trace[0]['args'][0] == 404) return array("status"=>0, "message"=>"Bad ID");
		else if ($trace[0]['args'][0] == 401) return array("status"=>0, "message"=>"Bad auth key"); 
		echo $e->getMessage();
		return null;
	}
	
	
	private function xml2array($xml) {
		$sxi = new SimpleXmlIterator($xml, null, false);
		return $this->sxiToArray($sxi);
	}
 
	private function sxiToArray($sxi) {
		$a = array();
		for( $sxi->rewind(); $sxi->valid(); $sxi->next() ) {
			
			if(!array_key_exists($sxi->key(), $a)) { $a[$sxi->key()] = array(); }
			if($sxi->hasChildren()){ $a[$sxi->key()][] = $this->sxiToArray($sxi->current()); }
			//else{ $a[$sxi->key()][] = strval($sxi->current()); }
			else{ $a[$sxi->key()] = strval($sxi->current()); }
		}
		return $a;
	}

	
}

?>

