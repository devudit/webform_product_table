<?php
/**
 * d8-webform
 *
 * @package     d8-webform
 * @author      Udit Rawat <uditrawat@fabwebstudio.com>
 * @license     GPL-2.0+
 * @link        http://fabwebstudio.com/
 * @copyright   Fab Web Studio
 * Date:        8/18/2018
 * Time:        10:56 AM
 */

namespace Drupal\webform_product_table\Commerce;


class ProductOperations {

  public static function calcualteTotal(){
    $session = \Drupal::request()->getSession();
    $cartItems = $session->get('wptCart');
    $total = 0;
    if(!empty($cartItems)){
      foreach ($cartItems as $key => $item){
        $price = floatval($item['product']->get('price'));
        $price = $price * $item['quantity'];
        if($item['discount']){
          $discount = round ( ($price * $item['discount']) / 100,2,PHP_ROUND_HALF_UP);
          $price = $price - $discount;
        }
        $total += $price;
      }
    }
    return $total;
  }

  public static function getProductPrice($product_id) {
    $product_total = 0;
    if($product_id){
      $session = \Drupal::request()->getSession();
      $cartItems = $session->get('wptCart');
      if(!empty($cartItems)){
        foreach ($cartItems as $key => $item){
          if($item['product']->get('originId') == $product_id){
            $product_total = floatval($item['product']->get('price')) * $item['quantity'];
          }
        }
      }
    }
    return $product_total;
  }

  protected static function getProduct($product_id){
    $session = \Drupal::request()->getSession();
    $cartItems = $session->get('wptCart');
    if(!empty($cartItems)){
      foreach ($cartItems as $key => $item){
        if($item['product']->get('originId') == $product_id){
          return $item['product'];
        }
      }
    }
    return null;
  }

  public static function setProductQuantity($product_id,$qty,$discount){
    if($product_id){
      $session = \Drupal::request()->getSession();
      $cartItems = $session->get('wptCart');
      if(!empty($cartItems)){
        foreach ($cartItems as $key => $item){
          if($item['product']->get('originId') == $product_id){
            $cartItems[$key]['quantity'] = $qty;
            $cartItems[$key]['discount'] = $discount;
          }
        }
      }
      $session->set('wptCart',$cartItems);
    }
  }

  public static function setProducts($products = []){
    $session = \Drupal::request()->getSession();
    $session->set('wptCart',[]);
    $wptCart = [];
    if(!empty($products)){
      foreach ($products as $key => $product){
        $wptCart[] = [
          'product' => $product,
          'quantity' => 0,
          'discount' => 0
        ];
      }
    }
    $session->set('wptCart',$wptCart);
  }

  public static function setProductDefaults($defaultValues){
    if(!empty($defaultValues)){
      $session = \Drupal::request()->getSession();
      $session->set('wptCart',[]);
      $wptCart = [];
      foreach ($defaultValues as $key => $values){
        $product = \Drupal::service('wpt.entity_helper')->getProduct($values['item_title']);
        $wptCart[] = [
          'product' => $product,
          'quantity' => $values['item_quantity'],
          'discount' => $values['item_discount']
        ];
      }
      $session->set('wptCart',$wptCart);
    }
  }

  public static function getItemTitle($product_id){
    $session = \Drupal::request()->getSession();
    $cartItems = $session->get('wptCart');
    if(!empty($cartItems)){
      foreach ($cartItems as $key => $item){
        if($item['product']->get('originId') == $product_id){
          return $item['product']->get('title');
        }
      }
    }
    return null;
  }

}