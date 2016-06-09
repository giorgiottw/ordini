<?php

$obj = json_decode(file_get_contents("php://input"), $assoc = true);

include("PrestashopWSH.php");
$psh = new PrestashopWSH(PS_SHOP_PATH,PS_WS_AUTH_KEY,DEBUG);

/*
$obj['data']=$obj['data'];
print_r($obj['data']);
exit();
*/
if(isset($obj['op']))
{
	switch($obj['op'])
	{
		/* PRODUCTS */
			/* ciao */
		case 'getproductbyreference' : echo json_encode($psh->getProductByReference($obj['data']['reference'], true)); break;
		case 'getproductbyid' : echo json_encode($psh->getProductById($obj['data']['id'], true)); break;
		case 'addproduct' : echo json_encode($psh->addProduct($obj['data'])); break;
		case 'delproductbyreference' : echo json_encode($psh->delProductByReference($obj['data']['reference'])); break;
		
		
		case 'getproductqty' : echo json_encode($psh->getProductQty($obj['data']['q'])); break;
		case 'setproductqty' : echo json_encode($psh->setProductQty($obj['data']['q'],$obj['data']['qty'],$search_by=$obj['data']['searchby'])); break;
	
		case 'getordersbystatus' : echo json_encode($psh->getOrdersByStatus($obj['data']['id_state'])); break;
			
		case 'setorderstatus' : echo json_encode($psh->setOrderStatus($obj['data']['id_order'], $obj['data']['id_state'])); break;
		
		case 'getordersbyids' : echo json_encode($psh->getOrdersByIds($obj['data'])); break;

		case 'getsupplierbyname' : echo json_encode($psh->getSupplierByName($obj['data'], $json=true)); break;
		case 'addsupplier' : echo json_encode($psh->getSupplierByName($obj['data'], $json=true)); break;
			
		case 'getmanufacturerbyname' : echo json_encode($psh->getManufacturerByName($obj['data'], $json=true)); break;
		case 'addmanufacturer' : echo json_encode($psh->addManufacturer($obj['data'], $json=true)); break;
		
		case 'getcategorybyname' : echo json_encode($psh->getCategoryByName($obj['data'], $json=true)); break;
		
		case 'addcategory' : {
			if(!isset($obj['data']['name']) || !isset($obj['data']['id_language']) ||
			   !isset($obj['data']['link_rewrite']) || !isset($obj['data']['id_home_category']))
			{
				echo json_encode(array("status"=>0, "message"=>"Wrong params for category.", ));
			}
			else echo json_encode($psh->addCategory($obj['data'], $json=true)); break;
		}
	}
}
else
{
	echo "Request is not valid.";
	
}

?>