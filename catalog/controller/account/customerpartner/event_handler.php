<?php
/**
 * @version [3.0.0.0] [Supported opencart version 3.x.x.x]
 * @category Webkul
 * @package Opencart-Marketplace Pro
 * @author [Webkul] <[<http://webkul.com/>]>
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
class ControllerAccountCustomerpartnerEventHandler extends Controller {
  /**
   * [private set error varibale in case of fault]
   * @var [type]
   */
  private $error = array();
  /**
   * [private set error flag for the conditions]
   * @var [type]
   */
  private $flag = true;
  /**
   * [private set status for the conditions]
   * @var [type]
   */
  private $status = false;
  private $price_status = false;
  private $quant_status = false;
  private $price_value = 0;
  private $quant_value = 0;
  /**
   * [private get all the requered configuration from the store]
   * @var [type]
   */
  private $config_setting = array();
  /**
   * [private product id of the requested item]
   * @var [type]
   */
  private $request_product_id = 0;

  private $config_limit_type = 0;
  private $config_limit_value = 0;
  /**
   * [private number of the products added into the cart]
   * @var [type]
   */
  private $request_product_quantity = 1;
  /**
   * [private all the products in the cart]
   * @var [type]
   */
  private $cart_products = array();
  /**
   * [private toatal number of the products in the cart added]
   * @var [type]
   */
  private $cart_total_products = 0;
  /**
   * [private store post request]
   * @var [type]
   */
  private $post = array();
  /**
   * [private store get request]
   * @var [type]
   */
  private $get = array();
  /**
   * [__construct get all the element ready befor we moove to any function so that we can avail all the requiered variable]
   * @param [type] $registory [description]
   */


  public function __construct($registory) {
 		parent::__construct($registory);

    $this->load->model('customerpartner/event_handler');

    $this->registry->set('ocutilities', new Ocutilities($this->registry));

    $this->data_helper = $this->model_customerpartner_event_handler;

    $this->setCartProducts();

    $this->setCartProductCount();
  }

  public function setCartProducts() {
     $this->cart_products = $this->ocutilities->getCartSellersProduct($this->cart->getProducts());
  }

  public function setCartProductCount() {
     $this->cart_total_products = $this->cart->countProducts();
  }

  public function getRequestVariables() {
     $this->get = $this->request->get;
  }

  public function postRequestVariables() {
     $this->post = $this->request->post;
  }

  public function isSellerProduct() {
   $this->flag = $this->data_helper->isSellerProduct($this->request_product_id);
  }

  public function setProductId() {
    if(isset($this->post['product_id'])){
       $this->request_product_id = $this->post['product_id'];
    }
  }

  public function setProductQuantity() {
    if(isset($this->post['quantity'])){
       $this->request_product_quantity = $this->post['quantity'];
    }
  }

  public function handlePostRequest(&$path, &$filter_data, &$products) {
     $filter_data['my_key'] = 1;
     $products['error'] = 2;
     $this->request->post['product_id'] = 0;
  }

  public function getCommonCartTotal() {
    $this->load->language('checkout/cart');
    // Unset all shipping and payment methods
    unset($this->session->data['shipping_method']);
    unset($this->session->data['shipping_methods']);
    unset($this->session->data['payment_method']);
    unset($this->session->data['payment_methods']);

    // Totals
    $this->load->model('setting/extension');

    $totals = array();
    $taxes = $this->cart->getTaxes();
    $total = 0;

    // Because __call can not keep var references so we put them into an array.
    $total_data = array(
      'totals' => &$totals,
      'taxes'  => &$taxes,
      'total'  => &$total
    );

    // Display prices
    if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
      $sort_order = array();

      $results = $this->model_setting_extension->getExtensions('total');

      foreach ($results as $key => $value) {
        $sort_order[$key] = $this->config->get($value['code'] . '_sort_order');
      }

      array_multisort($sort_order, SORT_ASC, $results);

      foreach ($results as $result) {
        if ($this->config->get('total_' . $result['code'] . '_status')) {
          $this->load->model('extension/total/' . $result['code']);

          // We have to put the totals in an array so that they pass by reference.
          $this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
        }
      }

      $sort_order = array();

      foreach ($totals as $key => $value) {
        $sort_order[$key] = $value['sort_order'];
      }

      array_multisort($sort_order, SORT_ASC, $totals);
    }

    return sprintf($this->language->get('text_items'), $this->cart->countProducts() + (isset($this->session->data['vouchers']) ? count($this->session->data['vouchers']) : 0), $this->currency->format($total, $this->session->data['currency']));
  }

  public function getLimitType() {
    $this->config_limit_type = $this->config->get('marketplace_product_purchase_limit_based_on');
  }

  public function getLimitValue() {
    if($this->config->get('marketplace_product_purchase_limit')){
      $this->config_limit_value = $this->config->get('marketplace_product_purchase_limit');
    }
  }

  public function addSCFilterJs(&$path = '', &$data = array(), &$output = false) {
    if ($this->config->get('module_marketplace_status')) {
      //add check for the category filter is enabled or not
      if ($this->config->get('marketplace_sc_filter_status')) {
       // place code in here after adding the feilda inside the marketplaces setiing ocnfig
      }
      $this->document->addScript('catalog/view/javascript/wk_marketplace/sf_category.js' ,'header');
    }
  }

  public function handleCartUpdate(&$path, &$filter_data) {

   $product_ids =  array();
   $manage_quant = 0;
    if (!empty($this->request->post['quantity'])) {
      foreach ($this->request->post['quantity'] as $key => $value) {

         $this->request_product_id = $this->data_helper->getProductId($key, $value);

         $this->request_product_quantity = $value;

         $manage_quant = $this->data_helper->getProductQuant($key, $value);
         /**
         * [$this->isSellerProduct check if product is belongs to any seller or admin]
         * @var [type]
         */
        $this->isSellerProduct();
        /**
         * [if if the marketplace module is enabled]
         * @var [type]
         */
        if ($this->flag && $this->config->get('module_marketplace_status')) {
           $this->flag = true;
        } else {
           $this->flag = false;
        }

      // check if seller has set restrcitcition for thier own product {
      $seller_restriction = $this->getSellerRestriction();

      if(!empty($seller_restriction) && $this->flag) {
        if (isset($seller_restriction['price_status']) && $seller_restriction['price_status'] && $this->config->get('marketplace_product_purchase_limit_priority')) {
            if (isset($seller_restriction['price']) && $seller_restriction['price']) {
              $this->price_status = true;
              $this->manageSellerRestrictionType('price');
              $this->price_value = $seller_restriction['price'];
            }
        }
        if (isset($seller_restriction['quant_status']) && $seller_restriction['quant_status'] && !$this->config->get('marketplace_product_purchase_limit_priority')) {
              if (isset($seller_restriction['quant']) && $seller_restriction['quant']) {
                $this->quant_status = true;
                $this->quant_value = $seller_restriction['quant'];
                $this->manageSellerRestrictionType('quant');
              }
        }
      }

      $this->seller_id = $this->ocutilities->getProductSellerID($this->request_product_id);

        if (($this->price_status && $this->price_value) || ($this->quant_status && $this->quant_value))  {

           if ($this->price_status && $this->price_value && $this->seller_id) {
               $total = 0;
               $this->config_limit_value = $this->currency->convert($this->price_value, 'USD', $this->session->data['currency']);
              // add up the total if already same product in the cart
               if(isset($this->cart_products[$this->seller_id][$this->request_product_id])) {
                 foreach ($this->cart_products[$this->seller_id][$this->request_product_id] as $key => $value) {
                    $total += $value['total'];
                 }
               }

               $price_q = 0;

               foreach ($this->cart->getProducts() as $key => $value) {
                 if(isset($value['cart_id']) && isset($this->request->post['quantity'][$value['cart_id']])) {
                    $price_q = $value['price'] * $this->request->post['quantity'][$value['cart_id']];
                 }
               }

               $total = $price_q;

               if ($total > $this->config_limit_value) {
                  $this->session->data['success'] =  '<b style="color:red;"> This product cant not be added to the cart as there is price limit for purchase the Sellers Product  </b><b style="color:black;"> You can not add product having price more than  '. $this->config_limit_value .' of total cart value </b>';
                  $this->request->post['quantity'] =  array();
                  $this->response->redirect($this->url->link('checkout/cart'));
                  $this->quant_status = false;
                  $this->quant_value = 0;
                  $this->flag = false;
               }
           }

           if ($this->quant_status && $this->quant_value && $this->seller_id) {
               $this->flag = false;
               $this->getLimitValue();

               $total = 0;

               $this->config_limit_value = $this->quant_value;

               $this->getLimitType();

               switch ($this->config_limit_type) {
                 case 1: // Based on the Total number of the product of the Single Seller
                     $total = 1 + count($this->cart->getProducts()) - $manage_quant;

                    if(!empty($this->cart_products) && ($total > $this->config_limit_value)) {
                      $this->session->data['success'] = '<b style="background-color:red;color:black;"> This product cant be added to the cart as there is limit for the Seller`s Product Quantity</b><b style="color:black;"> You can not add more than '. $this->config_limit_value .' product into the cart for the same seller</b>';
                      $this->request->post['quantity'] =  array();

                       $this->response->redirect($this->url->link('checkout/cart'));
                    }

                 break;
                 case 0: // Based on the Quantity of the single product
                       $this->seller_id = $this->ocutilities->getProductSellerID($this->request_product_id);
                       if ($this->seller_id) {
                         $total = $this->request_product_quantity;
                         if ($total > $this->config_limit_value) {
                            $this->session->data['success'] = '<b style="color:red;"> This product cant be added to the cart as there is limit for the number of the products Sellers Product  </b><b style="color:black;"> You can not add more than '. $this->config_limit_value .' product </b>';
                            $this->request->post['quantity'] =  array();
                            $this->response->redirect($this->url->link('checkout/cart'));
                         }
                       }
                 break;
                 default:
                 break;
               }
           }
        }
        // this is for the Default Flow.It will check the PRice restrcitons
         if ($this->flag) {
           $this->seller_id = $this->ocutilities->getProductSellerID($this->request_product_id);
      // if configuration enable the code will work
           if ($this->config->get('marketplace_product_purchase_limit_pra_seller') && $this->config->get('marketplace_product_purchase_price_limit') && $this->seller_id) {
               $total = 0;
               $this->config_limit_value = $this->currency->convert($this->config->get('marketplace_product_purchase_price_limit'), 'USD', $this->session->data['currency']);

              // add up the total if already same product in the cart
               if(isset($this->cart_products[$this->seller_id][$this->request_product_id])) {
                 foreach ($this->cart_products[$this->seller_id][$this->request_product_id] as $key => $value) {
                    $total += $value['total'];
                 }
               }
               $price_q = 0;

               foreach ($this->cart->getProducts() as $key => $value) {
                 if(isset($value['cart_id']) && isset($this->request->post['quantity'][$value['cart_id']])) {
                    $price_q = $value['price'] * $this->request->post['quantity'][$value['cart_id']];
                 }
               }

               $total = $price_q;

               if ($total > $this->config_limit_value) {
                 $this->session->data['success'] =  '<b style="color:red;"> This product cant not be added to the cart as there is limit for the number of the Sellers Product  </b><b style="color:black;"> You can not add product having price more than  '. $this->config_limit_value .' of total cart value </b>';
                 $this->request->post['quantity'] =  array();
                  $this->response->redirect($this->url->link('checkout/cart'));
                  $this->quant_status = false;
                  $this->quant_value = 0;
                  $this->flag = false;
               }
           }
        }
        if ($this->flag && $this->config->get('marketplace_product_purchase_limit')) {

         $this->getLimitType();

         $this->getLimitValue();

         switch ($this->config_limit_type) {
           case 1: // Based on the Total number of the product of the Single Seller
               $total = 1 + count($this->cart->getProducts()) - $manage_quant;

              if(!empty($this->cart_products) && ($total > $this->config_limit_value)) {
                $this->session->data['success'] = '<b style="background-color:red;color:black;"> This product cant be added to the cart as there is limit for the Seller`s Product Quantity</b><b style="color:black;"> You can not add more than '. $this->config_limit_value .' product into the cart for the same seller</b>';
                $this->request->post['quantity'] =  array();
       				  $this->response->redirect($this->url->link('checkout/cart'));
              }

           break;
           case 0: // Based on the Quantity of the single product
                 $this->seller_id = $this->ocutilities->getProductSellerID($this->request_product_id);
                 if ($this->seller_id) {
                   $total = $this->request_product_quantity;
                   if ($total > $this->config_limit_value) {
                      $this->session->data['success'] = '<b style="color:red;"> This product cant be added to the cart as there is limit for the number of the products Sellers Product  </b><b style="color:black;"> You can not add more than '. $this->config_limit_value .' product </b>';
                      $this->request->post['quantity'] =  array();
                      $this->response->redirect($this->url->link('checkout/cart'));
                   }
                 }
           break;
           default:
           break;
         }
        }
      }
    }
  }

  public function handleCartRequest(&$path, &$filter_data) {

    $this->flag = false;
    /**
     * [$this->getRequestVariables get all the Get Request varible from the request method]
     * @var [type]
     */
    $this->getRequestVariables();
    /**
     * [$this->postRequestVariables get all the Post Request varible from the POSt request method]
     * @var [type]
     */
    $this->postRequestVariables();
    /**
     * [$this->setProductId Set request post id in the local register]
     * @var [type]
     */
    $this->setProductId();
    /**
     * [$this->setProductQuantity set quantity in the local register]
     * @var [type]
     */
    $this->setProductQuantity();
    /**
     * [$this->isSellerProduct check if product is belongs to any seller or admin]
     * @var [type]
     */
    $this->isSellerProduct();
    /**
     * [if if the marketplace module is enabled]
     * @var [type]
     */
    if ($this->flag && $this->config->get('module_marketplace_status')) {
       $this->flag = true;
    }

    // check if seller has set restrcitcition for thier own product {
    $seller_restriction = $this->getSellerRestriction();

    if(!empty($seller_restriction) && $this->flag) {
      if (isset($seller_restriction['price_status']) && $seller_restriction['price_status'] && $this->config->get('marketplace_product_purchase_limit_priority')) {
          if (isset($seller_restriction['price']) && $seller_restriction['price']) {
            $this->price_status = true;
            $this->manageSellerRestrictionType('price');
            $this->price_value = $seller_restriction['price'];
          }
      }
      if (isset($seller_restriction['quant_status']) && $seller_restriction['quant_status'] && !$this->config->get('marketplace_product_purchase_limit_priority')) {
            if (isset($seller_restriction['quant']) && $seller_restriction['quant']) {
              $this->quant_status = true;
              $this->quant_value = $seller_restriction['quant'];
              $this->manageSellerRestrictionType('quant');
            }
      }
    }


    if (($this->price_status && $this->price_value) || ($this->quant_status && $this->quant_value))  {

       $this->seller_id = $this->ocutilities->getProductSellerID($this->request_product_id);
       if ($this->price_status && $this->price_value && $this->seller_id) {

           $total = 0;
           $this->config_limit_value = $this->currency->convert($this->price_value, 'USD', $this->session->data['currency']);
          // add up the total if already same product in the cart
           if(isset($this->cart_products[$this->seller_id])) {
             foreach ($this->cart_products[$this->seller_id] as $key => $value) {
               foreach ($value as $keys => $vas) {
                $total += $vas['total'];
              }
             }
           }

           $total = $total + $this->ocutilities->getProductTotal();

           if ($total > $this->config_limit_value) {
              $this->session->data['error_order_limit'] = '<b style="color:red;"> This product can not be added to the cart as there is limit for the number of the Seller Product  </b><b style="color:black;"> You can not add product having price more than  '. $this->config_limit_value .' of total cart value </b>';
              $this->session->data['common_cart_total'] = $this->getCommonCartTotal();
           }
           $this->quant_status = false;
           $this->quant_value = 0;
       }

       if ($this->quant_status && $this->quant_value && $this->seller_id) {

           $total = 0;

           $this->config_limit_value = $this->quant_value;

           switch ($this->config->get('marketplace_product_purchase_limit_based_on')) {
             case 1: // Based on the Total number of the product of the Single Seller
                 $total = 1;
                  foreach ($this->cart->getProducts() as $key => $value) {
                    if($this->ocutilities->getProductSellerID($value['product_id'])){
                      $total += 1;
                    }
                  }

                if(!empty($this->cart_products) && ($total > $this->config_limit_value)) {
                  $this->session->data['error_order_limit'] = '<b style="color:red;"> This product can not be added to the cart as there is limit for the Seller`s Product Quantity</b><b style="color:black;"> You can not more than '. $this->config_limit_value .' product into the cart for the same seller</b>';
                  $this->session->data['common_cart_total'] = $this->getCommonCartTotal();
                }
                   $this->flag = false;
             break;
             case 0: // Based on the Quantity of the single product
                      $this->seller_id = $this->ocutilities->getProductSellerID($this->request_product_id);

                     // add up the total if already same product in the cart
                      if(isset($this->cart_products[$this->seller_id][$this->request_product_id])) {
                        foreach ($this->cart_products[$this->seller_id][$this->request_product_id] as $key => $value) {
                           $total += $value['count'];
                        }
                      }
                      $total = $total + $this->request_product_quantity;

                      if ($total > $this->config_limit_value) {
                         $this->session->data['error_order_limit'] = '<b style="color:red;"> This product can not be added to the cart as there is limit for the number of the Sellers Product  </b><b style="color:black;"> You can not add more than '. $this->config_limit_value .' products </b>';
                         $this->session->data['common_cart_total'] = $this->getCommonCartTotal();
                      }
                        $this->flag = false;
             break;
             default:
             break;
           }

           // $this->flag = false;
       }
    }

    if(isset($this->session->data['error_order_limit']) && isset($this->session->data['common_cart_total'])) {
        $this->flag = false;
    }

   // this is for the Default Flow.It will check the PRice restrcitons
    if ($this->flag) {
      $this->seller_id = $this->ocutilities->getProductSellerID($this->request_product_id);
      if ($this->config->get('marketplace_product_purchase_price_limit') && $this->seller_id) {
          $total = 0;
          $this->config_limit_value = $this->currency->convert($this->config->get('marketplace_product_purchase_price_limit'), 'USD', $this->session->data['currency']);
         // add up the total if already same product in the cart
          if(isset($this->cart_products[$this->seller_id][$this->request_product_id])) {
            foreach ($this->cart_products[$this->seller_id][$this->request_product_id] as $key => $value) {
               $total += $value['total'];
            }
          }
          $total = $total + $this->ocutilities->getProductTotal();

          if ($total > $this->config_limit_value) {
             $this->session->data['error_order_limit'] = '<b style="color:red;"> This product cant not be added to the cart as there is limit for the number of the Sellers Product  </b><b style="color:black;"> You can not add product having price more than  '. $this->config_limit_value .' of total cart value </b>';
             $this->session->data['common_cart_total'] = $this->getCommonCartTotal();
          }
          $this->quant_status = false;
          $this->quant_value = 0;
          // $this->flag = false;
      }
    }

    if(isset($this->session->data['error_order_limit']) && isset($this->session->data['common_cart_total'])) {
        $this->flag = false;
    }

    if ($this->flag && $this->config->get('marketplace_product_purchase_limit')) {

     $this->getLimitType();

     $this->getLimitValue();

     $this->config_limit_value = $this->config->get('marketplace_product_purchase_limit');

     switch ($this->config->get('marketplace_product_purchase_limit_based_on')) {
       case 1: // Based on the Total number of the product of the Single Seller
           $total = 1 + count($this->cart->getProducts());

           foreach ($this->cart->getProducts() as $key => $value) {
            if($value['product_id'] == $this->request_product_id){
              $total = $total - 2;
            }
          }

          if(!empty($this->cart_products) && ($total > $this->config_limit_value)) {
            $this->session->data['error_order_limit'] = '<b style="color:red;"> This product can not be added to the cart as there is limit for the Seller`s Product Quantity</b><b style="color:black;"> You can not add more than '. $this->config_limit_value .' product into the cart for the same seller</b>';
            $this->session->data['common_cart_total'] = $this->getCommonCartTotal();
          }

       break;
       case 0: // Based on the Quantity of the single product
             $this->seller_id = $this->ocutilities->getProductSellerID($this->request_product_id);

             $total = 0;

             if ($this->seller_id) {
              // add up the total if already same product in the cart
               if(isset($this->cart_products[$this->seller_id][$this->request_product_id])) {
                 foreach ($this->cart_products[$this->seller_id][$this->request_product_id] as $key => $value) {
                    $total += $value['count'];
                 }
               }
               $total = $total + $this->request_product_quantity;
               if ($total > $this->config_limit_value) {
                  $this->session->data['error_order_limit'] = '<b style="color:red;"> This product cant not be added to the cart as there is limit for the number of the Sellers Product  </b><b style="color:black;"> You can not add more than '. $this->config_limit_value .' products </b>';
                  $this->session->data['common_cart_total'] = $this->getCommonCartTotal();
               }
             }
       break;
       default:
       break;
     }
    }

    // if (isset($this->request->post['product_id'])) {
    //   $product_id = (int)$this->request->post['product_id'];
    // } else {
    //   $product_id = 0;
    // }
    //
    // $this->load->model('catalog/product');
    //
    // $product_info = $this->model_catalog_product->getProduct($product_id);
    //
    // if ($product_info) {
    //   if (isset($this->request->post['quantity'])) {
    //     $quantity = (int)$this->request->post['quantity'];
    //   } else {
    //     $quantity = 1;
    //   }
    //
    //   if (isset($this->request->post['option'])) {
    //     $option = array_filter($this->request->post['option']);
    //   } else {
    //     $option = array();
    //   }
    //
    //   $product_options = $this->model_catalog_product->getProductOptions($this->request->post['product_id']);
    //
    //   foreach ($product_options as $product_option) {
    //     if ($product_option['required'] && empty($option[$product_option['product_option_id']])) {
    //         unset($this->session->data['error_order_limit']);
    //         unset($this->session->data['common_cart_total']);
    //     }
    //   }
    // }

  }

  public function getSellerRestriction() {
    $this->load->model('account/customerpartner/restriction');
    return $this->model_account_customerpartner_restriction->getRestrictions($this->customer->getId());
  }

  public function manageSellerRestrictionType($type = '') {
    if ($type) {
     switch ($type) {
       case 'price': // Based on the Total number of the product of the Single Seller
             if ($this->config->get('marketplace_product_purchase_limit_qra_seller')) {
                $this->flag = true;
             } else {
               $this->price_status = false;
               $this->quant_status = true;
             }

       break;
       case 'quant': // Based on the Quantity of the single product
             if ($this->config->get('marketplace_product_purchase_limit_qra_seller')) {
                 $this->flag = true;
             } else {
               $this->price_status = true;
               $this->quant_status = false;
             }
       break;
       default:
       break;
     }
  }
}

}
