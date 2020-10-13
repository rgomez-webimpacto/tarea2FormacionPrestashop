<?php
/**
* 2007-2020 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2020 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class importarproductoscsv extends Module
{
    public function __construct()
    {
        $this->name = 'importarproductoscsv';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Rubén Gómez Romero-Nieva';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Modulo Importacion de productos');
        $this->description = $this->l('Modulo importacion de productos mediante archivo csv.');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    public function install()
    {
        if (!parent::install()) {
            return false;
        }
        return true;
    }
    public function uninstall()
    {
        if (!parent::uninstall()) {
            false;
        }
        
        return true;
    }

    public function getContent()
    {
        $this->_html =$this->displayName;
    	if (isset($_POST['subirarchivocsv'])) {
            $target_file = $target_dir . basename($_FILES["archivocsv"]["name"]);

            $FileType = pathinfo($target_file, PATHINFO_EXTENSION);
            
            if ($FileType != "csv") {
                $this->_html .= $this->displayError($this->l('El archivo debe ser .csv').$target_file);
            } else {
                
                if ($file_content = Tools::file_get_contents($_FILES["archivocsv"]["tmp_name"])){
                    $separator = chr(10);
                    $lines = explode($separator,$file_content);

                    foreach($lines as $key=>$line){
                        if (empty($dat[0]) && !isset($lines[$key+1])) {
                            continue;
                            
                        }
	                    $data = $lines[$key+1];
	                    $product = explode(",", $data);

	                    foreach ($product as $key => $pro) {
	                    	
	                    	if ($key == 0) {
	                    		$nombre = $product[$key];
	                    	}
	                    	if ($key == 1) {
	                    		$reference = $product[$key];
	                    	}
	                    	if ($key == 2) {
	                    		$ean13 = $product[$key];
	                    	}
	                    	if ($key == 3) {
	                    		$precio_coste = $product[$key];
	                    	}
	                    	if ($key == 4) {
	                    		$precio_venta = $product[$key];
	                    	}
	                    	if ($key == 5) {
	                    		$iva = $product[$key];
	                    	}
	                    	if ($key == 6) {
	                    		$cantidad = $product[$key];
	                    	}
	                    	if ($key == 7) {
	                    		$categoria = $product[$key];
	                    		$cat = explode(";", $categoria);
	                    	}
	                    	if ($key == 8) {
	                    		$otra_categoria = $product[$key];
	                    	} 
	                    	$this->insertarDatos($nombre,$reference,$ean13,$precio_coste,$precio_venta,$iva,$cantidad,$cat,$otra_categoria);                      
                    	}
                    }
                    
                    
                    $this->_html .= $this->displayConfirmation($this->l('Importado correctamente.'));
                } else {
                    $this->_html .= $this->displayError($this->l('Error al subir el archivo '));
                }

            }
        }
        $this->_displayForm();

        return $this->_html;
    }

    private function _displayForm()
    {
        $tiendas = Shop::getShops();
        
        $this->_html .='';

      
		$this->_html .= '<div class="form-group">
        <form method="post" action="'.$_SERVER['REQUEST_URI'].'" enctype="multipart/form-data">
            <div class="alert alert-primary">'.$this->l('Importación de productos por csv').'</div>
            <div class="custom-file">
                <input type="file" class="custom-file-input" id="customFileLang" lang="es" name="archivocsv">
                <label class="custom-file-label" for="customFileLang" value="'.$this->l('Seleccionar Archivo csv').'"></label>
            </div>
            <div>
                <button class="btn btn-primary" type="submit" name="subirarchivocsv" >'.$this->l('Subir archivo').'</button>
            </div>
        </form>
    	</div>';
    }

    private function insertarDatos($nombre,$reference,$ean13,$precio_coste,$precio_venta,$iva,$cantidad,$cat,$otra_categoria)
    {
    	$id_lang=1;
    	$id_shop=1;
    	$active= 1;
    	$manofacture = 1;
    	$id_supplier = 1;
    	$redirect = '404';
    	$producto_existe = null;
    	$existe_cat = null;
    	$existe_cat_otra=null;
    	$i=0;

    	//ya existe producto
        $producto_existe= (db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'product WHERE reference = "'.$reference.'" '));

        if ($producto_existe == null) {
        	// recorremos las categorias 
        	foreach ($cat as $key => $value) {
        		$existe_cat = (db::getInstance()->getRow('SELECT id_category FROM '._DB_PREFIX_.'category_lang WHERE name = "'.$cat[$key].'" AND id_lang ="'.$id_lang.'"'));
        		if ($existe_cat != null) {
        			
        			break;
        		}
        	}
        	if($existe_cat == null){
        		
        		$existe_cat = (db::getInstance()->getRow('SELECT id_category FROM '._DB_PREFIX_.'category_lang WHERE name = "'.$otra_categoria.'" AND id_lang ="'.$id_lang.'"'));
        	}

        	$sql = 'INSERT INTO '._DB_PREFIX_.'product (id_supplier,id_manufacturer,id_category_default,ean13, reference, price, active,redirect_type) VALUES ("'.$id_supplier.'","'.$manofacture.'","'.$existe_cat['id_category'] .'" ,"'.$ean13.'", "'.$reference.'", "'.$precio_coste.'", "'.$active.'","'.$redirect.'")';
         	Db::getInstance()->execute($sql);
         	$id_product = Db::getInstance()->Insert_ID();

         	$sql = 'INSERT INTO '._DB_PREFIX_.'product_lang (id_product, id_shop, id_lang , name) VALUES ("'.$id_product.'","'.$id_shop.'","'.$id_lang.'","'.$nombre.'")';
         	Db::getInstance()->execute($sql);

         	$sql = 'INSERT INTO '._DB_PREFIX_.'product_shop (id_product,id_shop,id_category_default, price, active,redirect_type) VALUES ("'.$id_product.'","'.$id_shop.'","'.$existe_cat['id_category'] .'" , "'.$precio_coste.'", "'.$active.'","'.$redirect.'")';
         	Db::getInstance()->execute($sql);

         	foreach ($cat as $key => $value) {

         		// existen las categorias
        		$existe_cat = (db::getInstance()->getRow('SELECT id_category FROM '._DB_PREFIX_.'category_lang WHERE name = "'.$cat[$key].'" AND id_lang ="'.$id_lang.'"'));
        		if ($existe_cat != null) {
        			$sql = 'INSERT INTO '._DB_PREFIX_.'category_product (id_category, id_product, position) VALUES ("'.$existe_cat['id_category'].'","'.$id_product.'","'.$i.'")';
         			Db::getInstance()->execute($sql);
         			$i++;
        			
        		}
        	}
        	$existe_cat_otra= (db::getInstance()->getRow('SELECT id_category FROM '._DB_PREFIX_.'category_lang WHERE name = "'.$otra_categoria.'" AND id_lang ="'.$id_lang.'"'));
        	
        	if ($existe_cat_otra != null) {
	        	$sql = 'INSERT INTO '._DB_PREFIX_.'category_product (id_category, id_product, position) VALUES ("'.$existe_cat_otra['id_category'].'","'.$id_product.'","'.$i.'")';
	         	Db::getInstance()->execute($sql);
        	}
        	$sql = 'INSERT INTO '._DB_PREFIX_.'stock_available (id_product, id_shop, quantity) VALUES ("'.$id_product.'","'.$id_shop.'","'.$cantidad.'")';
	         Db::getInstance()->execute($sql);

        }else{ 

        }
  	
    }

}