<?php
    require('config.php');
    require_once('auth.php');
    $USPSID="566GANOS8109";
    //$UPSID;
    //$FEDEXID;
        $user = check_cookie( );
        if ( $expired ) { $user = null; $owner=false; } 
        if ( $expired || $user == null ) { redirect("relog_form.php"); die(); }
        
    //Displays all customer orders
    //$HC is buyer HC.
    //$pageNum is page number.
    function showOrderList($HC, $pageNum){
        $orders=find_like("Orders", "r_Buyer", $HC, "ORDER BY order_date DESC");
        if (is_null($orders)){
            echo "You have no completed orders.";
        }
        else{
            $orders=table_to_array($orders);
            $k=0;
            $last=true;
            $temp;
            for ($k=0;$k<sizeof($orders);$k++){//checks for orders that have been completed.
                $last=flag($orders[$k]['flags'], ORDER_COMPLETE);
                if ($last==true){
                    $temp[$k]=$orders[$k];
                }
            }
            $orders=$temp;
            $size=sizeof($orders);
            if ($size>15){
                $pagesize=15;
            }
            else $pagesize=$size;
            
            $start=($pageNum-1)*$pagesize+1;
            $end=$pagesize*$pageNum;
            echo '<table width="100%" cellpadding=5 cellspacing=0><tr><th>Order Number</th><th>Order Date</th><th>Shipped?</th></tr><tr style="background-color: #ACA;"><td>Showing '.$start.'-'.$end.' of '.$size.' '.($size>1?'results':'result').'</td><td>Page: ';
            for($j=1;$j<=($size/15.0)+1;$j++){
                echo '<a href="./order_review.php?page='.$j.'" style="color: '.($j==$pageNum?'#000':'#c63').'">'.$j.'</a>';
            }
            echo'</td></tr>';
            for ($i=0;$i<$pagesize; $i++){
                echo '<tr style="background-color: '.($i%2==0 ? '#9C6':'#9C9').';">';
                echo '<td><a href="./order_review.php?orderHC='.$orders[$i]['HC'].'">'.$orders[$i]['HC'].'</a></td>';
                echo '<td>'.date($orders[$i]['order_date']).'</td>';
                $products=getProducts($orders[$i]['HC']);
                $product1=getProduct($products[0]['r_Product']);
                $owner=getStoreOwnerHC($product1['r_Supplier']);
                if (is_null($orders[$i]['r_Shipment'])){
                    echo '<td>Not yet shipped! <a href="./send.php?HC='.$owner.'">Contact Merchant</a></td>';
                }
                else{
                    $shipment=find_like("Shipment", "HC", $orders[$i]['r_Shipment']);
                    $shipment=table_to_array($shipment);
                    echo '<td>Shipped on '.$shipment[0]['ship_date'].' at '.$shipment[0]['ship_time'].'<br />';
                    if (!is_null($shipment[0]['tracking_number'])){
                        echo '<a href="#">'.$shipment[0]['tracking_number'].' ('.$shipment[0]['shipping_provider'].')</a>';
                    }
                    echo '</td>';
                }
                echo '</tr>';
            }
            echo '</table>';
        }
    }
    
    //displays an order for shipping and tax calculations.
    function displayOrder($orderHC, $ship=0){
         setlocale(LC_MONETARY, "en_US");
        $products=getProducts($orderHC);
        echo '<table width="100%" cellpadding=5 cellspacing=0><tr><th>Product</th><th>Quantity</th><th>Price</th></tr>';
        $total;
        $freight=0;
        if ($ship!=0){
            echo '<tr>Enter Shipping Zipcode: <input id="shipZip" type="text" value="" onchange="getShipping(\''.$orderHC.'\');" /></tr>';
        }
        for ($i=0;$i<sizeof($products);$i++){
            $product=getProduct($products[$i]['r_Product']);
            $price=($product['price']*$products[$i]['qty']);
            echo '<tr style="background-color: '.($i%2==0 ? '#9C6':'#9C9').';">';
            echo '<td><a href="./product.php?HC='.$product['HC'].'">'.$product['name'].'</a></td>';
            echo '<td>'.$products[$i]['qty'].'</td>';
            echo '<td>'.money_format("%n", $price).'</td>';
            echo '</tr>';
            $total+=$price;
        }
        
        $tax=0;
        $total=$total+$tax+$freight;
        echo '<input type="hidden" id="subtotal" value="'.$total.'" />';
        echo '<input type="hidden" id="total" value="'.$total.'" />';
        echo '<input type="hidden" id="tax" value="'.$tax.'" />';
        echo '<input type="hidden" id="freight" value="'.$freight.'" />';
        echo '<tr style="background-color: #999;"><td></td><td></td><td>Tax: <span id="TaxAmount">'.$tax.'</span></td></tr>';
        echo '<tr style="background-color: #CCC;"><td></td><td></td><td>Freight: <span id="TotalShippingCost">'.$freight.'</span></td></tr>';
        echo '<tr style="background-color: #999;"><td><a href="./order_review.php" style="color: #13F;">Back to order review.</a></td>
                <td><a href="./send.php?HC=d8g2tslD" style="color: #13F;">Send in a ticket.</a></td>
                <td>Total: <span id="totalDisplay">'.$total.'</span></td></tr>';
        echo '<tr style="background-color: #CCC;"><td><a href="./cart.php" style="color: #13F;">Shopping Cart</a></td></tr>';
        echo '</table>';
    }
    
    //returns a list of products for receipt purposes
    function getOrderProductList($orderHC){
        $products=getProducts($orderHC);
        $retVal="";
        foreach ($products as $product){
            $prod=getProduct($product['r_Product']);
            $retVal.=$product['qty']." ".$prod['name']." : ".($prod['price']*$product['qty'])."<br />";
        }
        return $retVal;
    }
    
    //returns order products by orderHC
    function getProducts($orderHC){
        $products=find_like("order_Product", "r_Order", $orderHC);
        $products=table_to_array($products);
        return $products;
    }
    
    //displays a catalog.
    //Parameters are:
    //$StoreHC: Store to be displayed, Gudagi default.
    //$displayType: 0=list [default], 1=grid
    //$orderBy: 0="name", 1="rel", 2="price"
    //$page: pageNumber, default is 1.
    //$asc_desc: sort order, "ASC" for ascending, "DESC" for descending.
    //$search: search string
    function displayCatalog($StoreHC="3d5X61ys", $displayType=0, $orderBy=0, $asc_desc="ASC", $page=1, $search=""){//default is gudagi store; default display type is list, default order is alphabetical.
        $storeInfo=getStoreInfo($StoreHC);
        $products=getStoreProducts($StoreHC, $orderBy, $asc_desc, $search);
        if (count($products)<1){
            echo 'No matching products in this catalog ('.$storeInfo['name'].')! <a href="./catalog.php?HC='.$StoreHC.'">Go Back!</a>';
        }
        else{
            echo '<h1>Welcome to '.$storeInfo['name'].'!</h1>';
            echo '<img src="./fmimages/lines.gif" title="Display as list" onclick="updateCatalog(\''."0".'\');" /> <img src="./fmimages/grid.gif" title="Display as grid" onclick="updateCatalog(\''."1".'\');" /><br />';
            echo 'Search Catalog: <input type="text" id="searchBar" value="" onchange="updateCatalog(\''.$displayType.'\', \''.$page.'\', \'1\')"/> <a style="color: #366;" onclick="reset();">Reset</a><br />';
            echo '<input type="hidden" id="currentSearch" value="'.$search.'"><br />';
                echo 'Order <select id="Order" onchange="updateCatalog();">
                <option name="order" value="0" '.($orderBy==0?"selected=\"true\"":"").'>Alphabetically</option>';
                if ($search!=""){
                    echo '<option name="order" value="1" '.($orderBy==1?"selected=\"true\"":"").'>By Relevance</option>';
                }
                echo '<option name="order" value="2" '.($orderBy==2?"selected=\"true\"":"").'>By Price</option>
                <option name="order" value="3" '.($orderBy==3?"selected=\"true\"":"").'>Alphabetically, By Category</option>
                </select><br /><br />';
                echo '<input type="hidden" id="displayType" value="'.$displayType.'">';
                echo '<div id="mainPanel" style="background-color: #903; width:100%; height: 100%;">';
                echo '<input type="hidden" id="storeHC" value="'.$StoreHC.'" /><input type="hidden" id="currentPage" value="'.$page.'" /><table id="storeProducts" style="width: 100%;">';
                if ($displayType==0){
                    $i=0;
                    echo '<tr style="background-color: #966; text-align: center;"><th>Product Name</th><th>Price</th><th></th></tr>';
                    foreach ($products as $product){
                        if ($i<30*($page-1)){
                            continue;
                        }
                        echo '<tr style="background-color: '.($i%2==0?'#6CC':'#FCC').';" ><td style="padding: 15px; text-align: center; font-size: 1.5em;"><a href="./product.php?HC='.$product['HC'].'" title="'.substr($product['description'], 0, 150).'">'.$product['name'].'</a></td><td style="text-align: center;">'.$product['price'].'</td><td style="text-align: center;"><a onclick="addToCart(\''.$product['HC'].'\')">Add to Cart</a></td></tr>';
                        $i++;
                        if ($i>30*$page){
                            break;
                        }
                    }
    
                    $count=count($products);
                    $count1=($count/30);
                    echo '<tr style="background-color: #999;"><td style="padding: 5px;">Page:';
                    for ($j=0;$j<$count1;$j++){
                        echo ('<a onclick="updateCatalog(\''.$displayType.'\', \''.($j+1).'\');">'.($j+1).'</a>');
                    }
                    echo '<td></tr>';
                }
                else if ($displayType==1){
                    $i=0;
                    foreach ($products as $product){
                        if ($i<30*($page-1)){
                            continue;
                        }
                        if ($i%3==0){
                            echo '<tr style="background-color: '.($i%2==0?'#6CC':'#FCC').'">';
                        }
                        echo '<td style="padding: 10px; font-size: 1.1em; text-align: center;"><a href="./product.php?HC='.$product['HC'].'" title="'.substr($product['description'], 0, 150).'">'.$product['name'].'</a><br />'.$product['price'].'<br /><a onclick="addToCart(\''.$product['HC'].'\')">Add to Cart</a></td>';
                        $i++;
                        if ($i%3==0){
                            echo '</tr>';
                        }
                        if ($i>30*$page){
                            break;
                        }
                    }
                    $count=count($products);
                    $count1=($count/30);
                    if ($i%3==0){
                        echo '<tr style="background-color: #999;">';
                    }
                    echo '<td style="padding: 5px; background-color: #999">Page:';
                    for ($j=0;$j<$count1;$j++){
                        echo ('<a href="./catalog.php?HC='.$StoreHC.'&Page='.($j+1).'">'.($j+1).'</a>');
                    }
                        echo '<td></td><td></td></tr>';
                }
                echo '</table>';
                echo '</div>';
        }
    }
    
    //displays product information for product display
    function displayProduct($productHC){
        $product=getProduct($productHC);
        echo '<div id="Container" style="background-color: #999; margin: 20px; padding: 10px;">
            <div id="Head" style="background-color: #A54; padding: 5px;"><h1>'.$product['name'].'</h1></div>
            <div id="media" style="background-color: #000; padding: 5px; margin-bottom: 3px;">'.$product['video_tour'].'</div>
            <div id="Information"style="background-color: #739; padding: 10px; margin-bottom: 3px;"><h2>'.$product['description'].'</h2><br /><h3>Price: $'.$product['price'].' per</h3><br /><small>Tags: '.$product['tags'].'</small><br /><br/>Enter zip code for estimated shipping cost: <input type="text" id="zip" value="" size="5" onchange="getEstimatedShipping(\''.$productHC.'\');" />  <span id="displayShipping"></span><br /></div>
            <input type="text" value="1" id="quantVal" size="1"/> <a style="font-size: 1.5em; color: #FFF;" onclick="addToCart(\''.$product['HC'].'\')">Add to Cart</a><span id="checkout"></span>
            <br />
            <br />
           <a href="./catalog.php?HC='.$product['r_Supplier'].'" style="color: #363">Return to Store</a>
        </div>';
    }
    
    //returns all information stored in the store table for the given HC
    function getStoreInfo($StoreHC){
        $store=find_like("Store", "HC", $StoreHC);
        $store=table_to_array($store);
        $m=array();
        foreach ($store as $s){
            $m=$s;
        }
        return $m;
    }
    
    //return all products in specified store, ordered by parameters.
    //$order values: 0="name", 1="rel", 2="price"
    //$asc_desc values: "ASC" for ascending, "DESC" for descending.
    //$search: search string
    function getStoreProducts($StoreHC, $order, $asc_desc="ASC", $search=""){
        switch ($order){
            case 0:
                $order="name";
                break;
            case 1:
                $order="rel";
                break;
            case 2:
                $order="price";
                break;
            case 3:
                $order="name";
                break;
            default:
                $order="name";
        }
        $products=find_like("Product", "r_Supplier", $StoreHC, "ORDER BY ".$order." ".$asc_desc);
        $products=table_to_array($products);
        if ($search!=""){
            $prodTemp=array();
            $search=strtolower($search);
            $regex=$search;
            $i=0;
            foreach ($products as $product){
                $relevance=0;
                $matches1=preg_match($regex, strtolower($product['name']));
                $matches2=preg_match($regex, strtolower($product['description']));
                $matches3=preg_match($regex, strtolower($product['tags']));
                echo $matches1;
                if ($matches1>0){
                    $relevance+=3;
                }
                if ($matches2>0){
                    $relevance+=1;
                }
                if ($matches3>0){
                    $relevance+=2;
                }
                $product['relevance']=$relevance;
                if ($relevance==0){
                    continue;
                }
                else{
                    $prodTemp[$i]=$product;
                    $i++;
                }
            }
            $products=$prodTemp;
            if ($order=="rel"){
                $products=subval_sort($products, 'relevance');
            }
        }
        return $products;
    }
    
    //multidimensional array sorting by subvalue
    function subval_sort($a,$subkey) {
        foreach($a as $k=>$v) {
                $b[$k] = $v[$subkey];
        }
        arsort($b, SORT_NUMERIC);
        foreach($b as $key=>$val) {
                $c[] = $a[$key];
        }
        return $c;
    }
    
    //return specified order product based on HC
    function getOrderProduct($orderProductHC){
        $orderProduct=find_like("order_Product", "HC", $orderProductHC);
        $orderProduct=table_to_array($orderProduct);
        foreach($orderProduct as $product){
            return $product;
        }
    }
    
    //return all order products wherein the seller or the origin match the requester HC
    function getOrderProductsSeller($orderHC, $storeHC){
        $order=getOrder($orderHC);
        $products=getProducts($orderHC);
        $tempArray=array();
        $i=0;
        foreach ($products as $product){
            if ($product["r_Store_Origin"]!=$storeHC||$product["r_Store_Sold"]!=$storeHC){
                continue;
            }
            else{
                $tempArray[$i]=$product;
                $i++;
            }
        }
        return $tempArray;
    }
    
    //returns the order associated with the specified HC
    function getOrder($orderHC){
        $order=find_like("Orders", "HC", $orderHC);
        $order=table_to_array($order);
        foreach ($order as $o){
            return $o;
        }
    }
    
    //returns the product information associated with the specified HC
    function getProduct($productHC){
        $product=find_like("Product", "HC", $productHC);
        $product=table_to_array($product);
        foreach($product as $i){
            $product=$i;
        }
        return $product;
    }
    
    //returns the owner HC of a given store.
    function getStoreOwnerHC($storeHC){
        $owner=find_like("Store", "HC", $storeHC);
        $owner=table_to_array($owner);
        foreach ($owner as $o){
            $owner=$o['r_Owner'];
        }
        return $owner;
    }
    
    //Modifies the inventory of an item. 
    function updateInventory($productHC, $sold=1, $val=1){
        $product=getProduct($productHC);
        $sales=$product['sales'];
        $qty=$product['quantity'];
        if (($sold==1)&&($qty!=0)){
            $qty-=$val;
            set("Product", $productHC, "quantity", $qty);
        }
        else {//return -1 for error involving 0 quantity product.
            return -1;
        }
        $sales++;
        set("Product", $productHC, "sales", $sales);
    }
    
    //returns remaining quantity of 
    function checkInventory($productHC){
        $product=getProduct($productHC);
        $qty=$product['quantity'];
        return $qty;
    }
    
    //NYI
    function displayCart($userHC){
        
    }
    
    //returns an array with all state sales tax rates.
    //Keys are state abbreviations, all caps.
    function stateTaxArray(){
        $stateArray=array("AL"=>0.04, "AK"=>0.00, "AZ"=>0.056, "AR"=>0.06, "CA"=>0.0825, "CO"=>0.029, "CT"=>0.06,
                        "DE"=>0.00,"FL"=>0.06, "GA"=>0.04, "HI"=>0.04, "ID"=>0.06, "IL"=>0.0625, "IN"=>0.07, "IA"=>0.06, "KS"=>0.053,
                        "KY"=>0.06, "LA"=>0.04, "ME"=>0.05, "MD"=>0.06, "MA"=>0.0625, "MI"=>0.06, "MN"=>0.6875, "MS"=>0.07, "MO"=>0.04225,
                        "MT"=>0.00, "NE"=>0.055, "NV"=>0.0685, "NH"=>0.00, "NJ"=>0.07, "NM"=>0.05, "NY"=>0.04, "NC"=>0.0575, "ND"=>0.05,
                        "0H"=>0.055, "OK"=>0.045, "OR"=>0.00, "PA"=>0.06, "RI"=>0.07, "SC"=>0.06, "SD"=>0.04, "TN"=>0.07, "TX"=>0.0625,
                        "UT"=>0.047, "VT"=>0.06, "WA"=>0.065, "WV"=>0.06, "WI"=>0.05, "WY"=>0.04, "DC"=>0.06);
        return $stateArray;
    }
    
    //returns all coupons used in an order in table_to_array format.
    function getOrderCoupons($orderHC){
        $orderCoupons=find_like("order_Coupons", "r_Order", $orderHC);
        $orderCoupons=table_to_array($orderCoupons);
        return $orderCoupons;
        
    }
    
    //retrieves cart information from HC user
    function getCart($HC){
        $cart=find_like("Cart", "r_Customer", $HC);
        $cart=table_to_array($cart);
        foreach ($cart as $c){
            return $c;
        }
    }
    
    //Adds an item to cart, or creates a cart and adds an item if there was no cart.
    function addToCart($ProductHC, $userHC, $qty=1){
        $cart=getCart($userHC);
        if (is_null($cart)){
            delete("Cart", "r_Customer", $userHC);
            $cartHC=hash_code("cart", 20);
            $orderHC=hash_code("order", 12);
            insert("Cart", "HC", $cartHC);
            set("Cart",$cartHC, "r_Customer", $userHC);
            set("Cart", $cartHC, "r_Order", $orderHC);
            set("Cart", $cartHC, "items", 1);
            insert("Orders", "HC", $orderHC);
            set("Orders", $orderHC, "r_Buyer", $userHC);
            $cart=getCart($userHC);
        }
        $orderProductHC=hash_code("order_product", 32);
        insert("order_Product", "HC", $orderProductHC);
        set("order_Product", $orderProductHC, "r_Product", $ProductHC);
        set("order_Product",$orderProductHC, "r_Order", $orderHC);
        set("order_Product", $orderProductHC, "qty", $qty);
        $products=getProducts($cart['r_Order']);
        if (true){
            $items=$cart['items']+1;
            set("Cart", $cart['HC'], "items", $items);
        }
    }
    
    //returns coupon information based on HC
    function getCoupon($couponHC){
        $coupon=find_like("Coupon", "HC", $couponHC);
        $coupon=table_to_array($coupon);
        foreach ($coupon as $value){
            $coupon=$value;
        }
        return $coupon;
    }
    
    //Sets up a coupon for a product, applied to orderProductHC
    function orderProductCoupon($orderProductHC){
        
    }
    
    //applies coupon to proper place (order/product/cart)
    function applyCoupon(){
        
    }
    function calculateTotal($orderHC, $carrier="default"){
        $products=getProducts($orderHC);
        for($i=0;$i<sizeof($products); $i++){
            
        }
    }
    
    //returns all product sales that are sold by this seller.
    function getStoreOrderProductsSold($storeHC){
        $storeSoldProducts=find_like("order_Product", "r_Store_Sold", $storeHC);
        $storeSoldProducts=table_to_array($storeSoldProducts);
        return $storeOrderProducts;
    }
    
    //returns all product sales that are owned by this original seller
    function getStoreOrderProductsOrigin($storeHC){
        $storeOriginProducts=find_like("order_Product", "r_Store_Origin", $storeHC);
        $storeOriginProducts=table_to_array($storeOriginProducts);
        return $storeOriginProducts;
    }
    
    //Displays table with store order information.
    function showPendingOrders($storeHC){
        $storeOrderProductsOrigin=getStoreOrderProductsOrigin($storeHC);
        $storeOrderProductsSold=getStoreOrderProductsSold($storeHC);
        $shippedTable=array();
        $i=0;
        echo '<table id="Pending" border="1px">';
        echo '<tr>Unshipped Orders</tr>';
        echo '<tr><th>Date Sold</th><th>Order ID</th><th>Product</th><th>Edit</th></tr>';
        foreach ($storeOrderProductsOrigin as $product){
            $order=getOrder($product['r_Order']);
            $shipmentInfo=find_like('Shipment', "HC", $product['r_Shipment']);
            $shipmentInfo=table_to_array($shipmentInfo);
            foreach ($shipmentInfo as $ship){
                if (flag($ship['flags'], SHIPMENT_SHIPPED)==false){
                    
                    if ($product['r_Store_Sold']==$product['r_Store_Origin']){
                        echo '<tr>';
                        echo '<td>'.date($order['order_date']).'</td>';//email
                        echo '<td><a href="./seller_view_order.php?orderHC='.$product['r_Order'].'&storeHC='.$storeHC.'" title="Look at all products you sold in this order.">'.$product['r_Order'].'</a></td>';
                        $prod=getProduct($product['r_Product']);
                        echo '<td><a href="./product.php?HC='.$product['r_Product'].'">'.$prod['name'].'</a></td>';
                        echo '<td><a href="./seller_editorderproduct.php?HC='.$product['HC'].'&storeHC='.$storeHC.'"><img src="./fmimages/salestag1.gif" title="Edit Shipment Information" /></a><br /><br /><a href="./seller_refund.php?HC='.$product['HC'].'"><img src="./fmimages/delete2.gif" title="Refund this product!" /></a><a href="./message.php?id='.$order['r_Buyer'].'"><img src="./fmimages/mail2.gif" title="Contact Buyer" /></a></td>';
                        echo '</tr>';
                    }
                    else{
                        echo '<tr>';
                        echo '<td>'.date($order['order_date']).'</td>';
                        echo '<td><a href="./seller_view_order.php?orderHC='.$product['r_Order'].'&storeHC='.$storeHC.'" title="Look at all products you sold in this order."'.$product['r_Order'].'</td>';
                        echo '<td><a href="./product.php?HC='.$product['r_Product'].'">'.$product['name'].'</a></td>';
                        echo '<td><a href="./seller_editorderproduct.php?HC='.$product['HC'].'&storeHC='.$storeHC.'"><img src="./fmimages/salestag1.gif" title="Edit Shipment Information" /></a><br /><br /><a href="./seller_refund.php?HC='.$product['HC'].'"><img src="./fmimages/delete2.gif" title="Refund this product!" /></a><a href="./message.php?id='.getStoreOwnerHC($product['r_Store_Sold']).'"><img src="./fmimages/mail2.gif" title="Contact Reseller" /></a></td>';
                        echo '</tr>';
                    }
                }
                else{
                    $shippedTable[$i]=$product;
                    $i++;
                }
            }
        }
        foreach ($storeOrderProductsSold as $product){
            $order=getOrder($product['r_Order']);
            $shipmentInfo=find_like('Shipment', "HC", $product['r_Shipment']);
            $shipmentInfo=table_to_array($shipmentInfo);
            foreach ($shipmentInfo as $ship){
                if (flag($ship['flags'], SHIPMENT_SHIPPED)==false){
                    if ($product['r_Store_Sold']==$product['r_Store_Origin']){
                        continue;
                    }
                    else{
                        $prod=getProduct($product['r_Product']);
                        echo '<tr>';
                        echo '<td>'.date($order['order_date']).'</td>';
                        echo '<td><a href="./seller_view_order.php?orderHC='.$product['r_Order'].'&storeHC='.$storeHC.'" title="Look at all products you sold in this order.">'.$product['r_Order'].'</a></td>';
                        echo '<td><a href="./product.php?HC='.$product['r_Product'].'">'.$prod['name'].'</a></td>';
                        echo '<td><a href="./seller_refund.php?HC='.$product['HC'].'"><img src="./fmimages/delete2.gif" title="Refund this product!" /></a><a href="./message.php?id='.getStoreOwnerHC($product['r_Store_Origin']).'"><img src="./fmimages/mail2.gif" title="Contact Seller" /></a></td>';
                        echo '</tr>';
                    }
                }
                else{
                    $shippedTable[$i]=$product;
                    $i++;
                }
            }
        }
        echo '</table><br /><br />';
        showShippedOrders($shippedTable, $storeHC);
    }
    
    //Shows all products from a given order that are directly sold by that seller.
    function showSellerOrder($orderHC, $storeHC){
        $order=getOrderProductsSeller($orderHC, $storeHC);
        $orderData=getOrder($orderHC);
        echo 'Order Date: '.$orderData['order_date'];
        echo '<table border="1px">';
        echo '<tr><th>Order Product</th><th>Tracking</th></tr>';
        foreach ($order as $product){
            echo '<tr>';
            echo '<td></td>';
            echo '</tr>';
        }
        echo '</table>';
    }
    
    //Parses options from an option string, derived from the database field in order products table.
    //Stores this information in an array.
    function parseOptions($optionString){
        $groupOptionSets=explode("|", $optionString);
        $groupArray=array();
        $i=0;
        foreach ($groupOptionSets as $grouping){ 
            $groupAndOpt=explode(":", $grouping);
            $group=getGroup($groupAndOpt[0]);
            $options=explode(",", $groupAndOpt[1]);
            $opts=array();
            $j=0;
            foreach ($options as $opt){
                $opts[$j]=getOption($opt);
                $j++;
            }
            $groupArray[$i]['group']=$group[0];
            $groupArray[$i]['options']=$opts;
            $i++;
        }
        return $groupArray;
    }
    
    //returns a single product group option based on HC
    function getOption($optionHC){
        $option=find_like("Product_options", "HC", $optionHC);
        $option=table_to_array($option);
        foreach ($option as $o){
            return $o;
        }
    }
    
    //returns limited contact information.
    //only returns name of contact specified.
    function getContactName($contactHC){
        $contact=find_like("Contact", "HC", $contactHC);
        $contact=table_to_array($contact);
        foreach($contact as $con){
            $co=$con['first_name']." ".$con['last_name'];
        }
        return $co;
    }
    
    //edit and review product shipment information based on orderProductHC
    function editOrderInfo($orderProductHC, $storeHC){
        $product=getOrderProduct($orderProductHC);
        $prod=getProduct($product['r_Product']);
        $shipInfo=getShipmentInfo($product['r_Shipment']);
        $shipAddress=getShippingAddress($shipInfo['r_shipping_Address']);
        $contact=getContactName($shipAddress['r_Contact']);
        echo 'Customer Name: <a href="./message.php?id='.$shipAddress['r_Contact'].'" title="Send '.$contact.' a message.">'.$contact.'</a><br />';
        echo 'Shipping Address: <br />';
        echo '<div style="background-color: #CCA; width:20%; padding: 6px;">';
        echo $shipAddress['name']."<br />";
        echo $shipAddress['company']."<br />";
        echo $shipAddress['address1']."<br />";
        echo $shipAddress['city'].", ";
        echo $shipAddress['state']." ";
        echo $shipAddress['zip']."<br />";
        echo '</div>';
        echo 'Product ID: <a href="./product.php?HC='.$product['r_Product'].'">'.$product['r_Product'].'</a>';
        echo '<br />';
        echo 'Product Name: '.$prod['name'];
        echo '<br />';
        if (!is_null($product['Options'])){
            $options=parseOptions($product['Options']);
            echo 'Product Options: <br />';
            echo '<table style="padding: 3px; background-color: #9C9; text-align: center;">';
            echo '<tr style="background-color:#963;"><th>Group Name </th><th>Group Code</th><th>Options Selected</th><th>Option Code(s)</th></tr>';
            foreach ($options as $groupSet){
                echo '<tr style="padding:2px;background-color: #C66;">';
                echo '<td>'.$groupSet['group']['name'].'</td>';
                echo '<td>'.$groupSet['group']['HC'].'</td>';
                $string="";
                $str2="";
                echo '<td style="margin-top: 3px;">';
                foreach ($groupSet['options'] as $option){
                    $str2.=$option['name'].'<br />';
                    $string.=$option['HC']."<br />";
                }
                echo $str2;
                echo '</td>';
                echo '<td>'.$string.'</td>';
                echo '</tr>';
            }
            echo '</table><br />';
        }
        echo 'Quantity: '.$product['qty'].'<br /><br />';
        echo 'Order shipped? <input type="checkbox" id="shipBox" onclick="shipToggle();"/>';
        echo '<br />';
        echo 'Tracking Number: <input type="text" value="" id="trackBox" /> <br /><br /><input type="button" value="Upload shipment information" onclick="updateShipping();" />';
        echo '<br />';
        echo '<br />';
        
    }
    
    //returns all information from the shipping table based on HC
    function getShipmentInfo($shipHC){
        $shipInfo=find_like("Shipment", "HC", $shipHC);
        $shipInfo=table_to_array($shipInfo);
        foreach ($shipInfo as $s){
            return $s;
        }
    }
    
    //displays tracking information in a panel based on tracking number and carrier
    //$carrier types: "UPS", "USPS", "FEDEX"
    function trackingPane($trackingNumber, $carrier){
        if ($carrier=="UPS"){
            trackUPS($trackingNumber);
        }
        else if ($carrier=="USPS"){
            trackUSPS($trackingNumber);
        }
        else if ($carrier=="FEDEX"){
            trackFEDEX($trackingNumber);
        }
    }
    
    //add tracking number by orderProductHC
    function editTracking($orderProductHC, $trackingNumber){
        $product=getOrderProduct($orderProductHC);
        $ship=getShipmentInfo($product['r_Shipment']);
        set("Shipment", $product['r_Shipment'], "tracking_number", $trackingNumber);
        now("Shipment", $product['r_Shipment'], "ship_time");
        now("Shipment", $product['r_Shipment'], "ship_date");
        //if not listed as shipped, activate shipped flag.
        if (!flag($ship['flags'], SHIPMENT_SHIPPED)){
            activate("Shipment", $product['r_Shipment'], "flags", SHIPMENT_SHIPPED);
        }
    }
    
    //remove shipping informaiton from a product
    function unship($orderProductHC){
        $product=getOrderProduct($orderProductHC);
        $ship=getShipmentInfo($product['r_Shipment']);
        set ("Shipment", $product['r_Shipment'], "tracking_number", NULL);
        deactivate("Shipment", $product['r_Shipment'], "flags", SHIPMENT_SHIPPED);
        return 1;
    }
    
    //customer product_options display
    function displayOptionCustomer($productHC){
        $product=getProduct($productHC);
        $group=getGroups($productHC);
    }
    
    //returns all groups associated with a product
    function getProductGroups($productHC){
        $groups=find_like("Product_option_group", "r_Product", $productHC);
        $groups=table_to_array($groups);
        $list="<select id='groups' name='groups'>";
        foreach ($groups as $group){
            $options=getProductOptions($group['HC']);
            $list=$list."<option value='".$group['HC']."'>".$group['name']."</option>";
        }
        $list=$list."</select>";
        return $list;
    }
    
    //returns all options associtated with a group
    function getProductOptions(){
        
    }
    
    //returns all option groups associated with the specified ownerHC
    function getGroups($ownerID){
        $result=find_like("Product_option_group","r_Owner",$ownerID);
        $result=table_to_array($result);
        $list="<select id='groups' name='groups'>";
        foreach ($result as $group){
            $list=$list."<option value='".$group['HC']."'>".$group['name']."</option>";
        }
        $list=$list."</select>";
        return $list;
    }
    
    //returns a single group by HC
    function getGroup($groupHC){
        $group=find_like("Product_option_group", "HC", $groupHC);
        $group=table_to_array($group);
        return $group;
    }
    
    //returns all options from a specified option group
    function getOptions($groupHC){
        $result=find_like("Product_options", "r_Product_option_group", $groupHC);
        $result=table_to_array($result);
        $list="<select id='options' name='options' multiple>";
        foreach ($result as $option){
           $list=$list."<option title='".$option['description']."'value='".$option['HC']."'>".$option['name']."</option>\n";
        }
        $list=$list."</select>";
        return $list;
    }
    
    //option editing pane.
    //$store values: 1[default],
    //$new values: 0[default], 
    function optionPane($productHC, $store=1, $new=0){
        $product=getProduct($productHC);
        $groups=array();
        if ($new!=0){
            $groups=find_like("Product_option_group", "r_Product", $productHC);
            $groups=table_to_array($groups);
        }
        $allGroups=array();
        if ($store==1){
            $allGroups=find_like("Product_option_group", "r_Owner", $user['HC']);
            $allGroups=table_to_array($allGroups);
        }
        $standardGroups=find_like("Product_option_group", "r_Owner", "standard");//NYI, will have standard groups to choose from.
        $standardGroups=table_to_array($standardGroups);
        echo '<div style="margin:20px; float:left; width: 40%">';
        echo '<div style="margin:20px;">';
        echo 'Typical Option Groups: <br />';
        echo '<select name="standard">';
        foreach ($standardGroups as $group){
            echo '<option name="standardGroupChoice" value="'.$group['HC'].'">'.$group['name'].'</option>';
        }
        echo '<input type="button" value="Add Group to Product" onclick="update(\'0\')" />';
        echo'</select> ';
        echo '<br />';
        echo '<br />';
        if (sizeof($allGroups)>0){
            echo '<select name="standard">';
            foreach ($allGroups as $group){
                echo '<option name="allGroupChoice" value="'.$group['HC'].'">'.$group['name'].'</option>';
            }
            echo'</select> ';
            echo '<input type="button" value="Add Group to Product" onclick="update(\'1\')" />';
        }

        echo '</div>';
        echo '<div style="margin:20px;">';
        echo 'Add a new Option Group<br />';
        echo 'Name: <input type="text" id="newGroupName" /><br />';
        echo 'Description: <input type="text" id="newGroupDescription" /><br />';
        echo '<input type="hidden" id="newGroupProductId" value="'.$_GET['id'].'" /><br />';
        echo '<input type="button" value="Add Group to Product" onclick="update(\'0\')" />';
        echo '</div>';
        echo '<div style="margin:20px;">';
        echo 'Add a new Group Value<br />';
        echo 'Name: <input type="text" id="newGroupName" /><br />';
        echo 'Description: <input type="text" id="newGroupDescription" /><br />';
        echo '<input type="hidden" id="newGroupProductId" value="'.$_GET['id'].'" /><br />';
        echo '<input type="button" value="Add Option to Product" onclick="update(\'0\')" />';
        echo '</div>';
        echo '</div>';
        echo '<div style="margin:20px; float: left; width: 40%;">';
        echo '<div style="margin:20px;>';
        echo 'Current Product Groups: <br /><small>Select one to see options</small><br />';
        echo '<form><select id="prodGroups" name="groupSelect" size=4>';
        echo '<option name="five" value="nine">Select Below!</option>';
        foreach ($groups as $group){
            echo '<option name="group" id="selectedGroup" value="'.$group['HC'].'">'.$group['name'].'</option>';
        }
        echo '</select></form>';
        echo '<input type="button" value="Remove this group!" onclick="depreciate();"/>';
        echo '</div><div id="GroupInfo"></div>';
        echo '<div style="margin:20px;>';
        echo 'Group Values: <br /><small>Select one to see options</small><br />';
        echo '<form><select id="prodGroups" name="groupSelect" size=4>';
        echo '<option name="five" value="nine">Select Below!</option>';
        foreach ($groups as $group){
            echo '<option name="group" id="selectedGroup" value="'.$group['HC'].'">'.$group['name'].'</option>';
        }
        echo '</select></form>';
        echo '<input type="button" value="Remove this group value!" onclick="depreciate();"/>';
        echo '</div><div id="ValueInfo"></div>';
        echo '</div>';
    }
    
    //create group in database
    //$name is group name
    //$desc is group description
    //$productID is HC of product to which group is applied
    //$ownerID is HC of group creator or user.
    function createGroup($name, $desc, $productID, $ownerID){
        $groupHC=hash_code("1",20);
        insert("Product_option_group", "HC", $groupHC);
        set("Product_option_group", $groupHC, "name", $name);
        set("Product_option_group", $groupHC, "description", $desc);
        set("Product_option_group", $groupHC, "r_Product", $productID);
        set("Product_option_group", $groupHC, "r_Owner", $ownerID);
        return getGroups($productID);
    }
    
    //Creates an option attached to a group
    //$name is option name
    //$qty is option quantity
    //$description is option description
    //$groupHC is HC of group to which option is applied
    function createOption($name, $qty, $description, $groupHC){
        $optionHC=hash_code("1",20);
        $name=$value;
        $qty=$_GET["quantity"];
        $description=$_GET["description"];
        insert("Product_options", "HC", $optionHC);
        set("Product_options", $groupHC, "name", $name);
        set("Product_options", $groupHC, "description", $description);
        set("Product_options", $groupHC, "qty", $qty);
        set("Product_options", $groupHC, "r_Product_option_group", $groupHC);
        return getOptions($groupHC);
    }
    
    //removes or depreciates group options in a list..
    function removeOptions($optionList, $groupHC){
        foreach ($optionList as $option){
            $o=getOption($option);
            if (!flag($o['flags'], PRODUCT_OPTIONS_SOLD)){
                delete("Product_options", "HC", $option);
            }
            else{
                activate("Product_options", $option,"flags", PRODUCT_OPTIONS_DEPRECIATED);
            }
        }
        return getOptions($groupHC);
    }
    
    //removes or depreciates an option group.
    function removeGroup($groupHC, $productHC){
        $group=getGroup($groupHC);
        if (!flag($group['flags'], PRODUCT_OPTION_SOLD)){
            delete("Product_option_group", "HC", $groupHC);
            delete("Product_options", "r_Product_option_group", $groupHC);
        }
        else {
            activate("Product_options");
        }
        getGroups($productHC);
    }
    
    //displays shipped products for a given store
    //$shippedArray is array of shipped products from the store
    //$storeHC is the store identifier
    function showShippedOrders($shippedArray, $storeHC){
        echo '<table id="Shipped_Orders" border="1px">';
        echo '<tr>Shipped Orders</tr>';
        echo '<tr><th>Tracking Number</th><th>Shipping Service</th><th>Date Shipped</th><th>Date Ordered</th><th>Order ID</th><th>Product</th><th>Edit Information</th></tr>';
        foreach ($shippedArray as $product){
            $order=getOrder($product['r_Order']);
            $shipmentInfo=find_like('Shipment', "HC", $product['r_Shipment']);
            $shipmentInfo=table_to_array($shipmentInfo);
            foreach ($shipmentInfo as $ship){
                $shipmentInfo=$ship;
            }
            $prod=getProduct($product['r_Product']);
            echo '<tr>';
            echo '<td>'.$shipmentInfo['tracking_number'].'</td>';
            echo '<td>'.$shipmentInfo['shipping_provider'].'</td>';
            echo '<td>'.date($shipmentInfo['ship_date']).'</td>';
            echo '<td>'.date($order['order_date']).'</td>';
            echo '<td><a href="./seller_view_order.php?orderHC='.$product['r_Order'].'&storeHC='.$storeHC.'" title="Look at all products you sold in this order.">'.$product['r_Order'].'</a></td>';
            echo '<td><a href="./product.php?HC='.$product['r_Product'].'">'.$prod['name'].'</a></td>';
            if ($product['r_Store_Origin']==$storeHC){
                echo '<td><a href="./seller_editorderproduct.php?HC='.$product['HC'].'&storeHC='.$storeHC.'"><img src="./fmimages/salestag1.gif" title="Edit Shipment Information" /></a><a href="./seller_refund.php?HC='.$product['HC'].'"><img src="./fmimages/delete2.gif" title="Refund this product!" /></a><a href="./message.php?id='.$order['r_Buyer'].'"><img src="./fmimages/mail2.gif" title="Contact Buyer" /></a></td>';
            }
            else {
                echo '<td><a href="./seller_refund.php?HC='.$product['HC'].'"><img src="./fmimages/delete2.gif" title="Refund this product!" /></a><a href="./message.php?id='.getStoreOwnerHC($product['r_Store_Origin']).'"><img src="./fmimages/mail2.gif" title="Contact Seller" /></a></td>';
            }
            echo '</tr>';
        }
        echo '</table>';
    }
    
    //returns all sales made by the store directly, not as a reseller.
    function getStoreSales($storeHC){
        $store=getStoreInfo($storeHC);
        $items=find_like("order_Product","r_Store_Origin", $storeHC);      
    }
    
    function updateOrderShipping($orderProductHC){
        
    }
    
    //updates Shipment table with address and order information. Used at checkout.
    function setShippingData($orderHC, $shipAddressHC){
        $products=getProducts($orderHC);
        foreach ($products as $product){
            $HC=hash_code("1", 16);
            insert("Shipment", "HC", $HC);
            set("order_Product", $product['HC'],"r_Shipment", $HC);
            set("Shipment", $HC, "r_shipping_Address", $shipAddressHC);
        }
    }
    
    //displays store order history.
    function getStoreOrders($storeHC){
        showPendingOrders($storeHC);
    }
    
    //determines tax rate based on zip code.
    function getTax($zip){
        $result=file_get_contents('http://production.shippingapis.com/ShippingAPITest.dll?API=CityStateLookup
                        &XML=<CityStateLookupRequest USERID="'.$USPSID.'"><ZipCode ID= "0">
                         <Zip5>90210</Zip5></ZipCode></CityStateLookupRequest>');
        $state=$result;
        $stateTax=array();
        $stateTax=stateTaxArray();
        $stateTax=$stateTax["PA"];
        return $stateTax;
    }
    
    //returns shipping cost per product requested
    //$orderProductHC is order product HC
    //$carrier values: "UPS", "USPS", "FEDEX"
    //$zip is requested zip code
    //$flag: 0[default], if flag is 1, $orderProductHC is used as ProductHC
    //  shipping is derived from the seller's zip code, which can be derived
    //  from the order product or from the product itself.
    function getShippingForProduct($orderProductHC, $carrier, $zip, $flag=0){//if flag==1, $orderProductHC=Product HC
        if ($flag!=0){
            $shipInfo=getShipInfo($orderProductHC);
        }
        else{
            $orderProduct=getOrderProduct($orderProductHC);
            $shipInfo=getShipInfo($orderProduct['r_Product']);
        }
        if ($carrier=="UPS"){
            $cost=getUPSCost($orderProductHC, $zip, $flag);
        }
        else if ($carrier=="FedEx"){
            $cost=getFedExCost($orderProductHC, $zip, $flag);
        }
        else if ($carrier=="USPS"){
            $cost=getUSPSCost($orderProductHC, $zip, $flag);
        }
        return $cost;
    }
    
    //determine shipping cost for an entire order
    function getShippingForOrder($orderHC, $zip){
        $products=getProducts($orderHC);
        $shippingCost=0;
        foreach($products as $product){
            $shippingCost+=getShippingForProduct($product['HC'], 'UPS', $zip);
        }
        return $shippingCost;
    }
    
    //updates shipping cost on an order_Product basis.  For use with individual products.
    function updateShipping($orderProductHC, $cost){
        set("order_Product", $orderProductHC, "Shipping", $cost);
    }
    
    function parseIt($input){
        $parser=xml_parser_create();
        $result=xml_parse_into_struct($parser, $input, $responseArray);
        xml_parser_free($parser);
        return $responseArray;
    }
    
    //Shipping Address is stored in the database
    //$userHC is customer HC
    //$address is address array
    //$store is default 0, wherein 1 denotes a willingness to save the address.
    function storeShippingAddress($userHC, $address, $store=0){
        $shipHC=hash_code("1", 8);
        insert("shipping_Address", "HC", $shipHC);
        set("shipping_Address", $shipHC, "name", $address[0]." ".$address[1]);
        set("shipping_Address", $shipHC, "company", $address[2]);
        set("shipping_Address", $shipHC, "address1", $address[3]);
        set("shipping_Address", $shipHC, "city", $address[4]);
        set("shipping_Address", $shipHC, "state", $address[5]);
        set("shipping_Address", $shipHC, "zip", $address[6]);
        set("shipping_Address", $shipHC, "r_Contact", $userHC);
        if ($store==1){
            activate("shipping_Address", $shipHC, "flags", SHIPPING_ADDRESS_SAVE);
        }
        return $shipHC;
    }
    
    //return all shipping addresses where SHIPPING_ADDRESS_SAVE is activated.
    function getShippingAddresses($userHC){
        $addressArray=find_like("shipping_Address", "r_Contact", $userHC);
        $addressArray=table_to_array($addressArray);
        $a=array();
        $i=0;
        foreach ($addressArray as $address){
            if (!flag($address['flags'], SHIPPING_ADDRESS_SAVE)){
                continue;
            }
            else{
                $a[$i]=$address;
            }
            $i++;
        }
        return $a;
    }
    
    //returns specified shipping address
    function getShippingAddress($addressHC){
        $address=find_like("shipping_Address", "HC", $addressHC);
        $address=table_to_array($address);
        $a=array();
        foreach ($address as $add){
            $a=$add;
        }
        return $a;
    }
    
    //display all saved shipping addresses of the current customer.
    function displayAddresses($userHC){
        $addresses=getShippingAddresses($userHC);
        echo '<table id="sAddress">';
        foreach($addresses as $address){
            echo'<tr><td>'.$address.'</td></tr>';
        }
        echo'</table>';
    }
    
    //returns the shipping options of a given product. NYI
    function getShipOptions($productHC){
        $product=getProduct($productHC);
    }
    
    //return the UPS cost for a given zip code and order product, ship
    //$flag 0 by default, if not then $orderProductHC is $ProductHC
    //$zip is dest zip code.
    //$optionArray contains extra shipping options, optional.
    function getUPSCost($orderProductHC, $zip, $flag=0, $optionArray=NULL){
        $product=array();
        $shipInfo=array();
        $OProduct=array();
        if ($flag!=0){
            $shipInfo=getShipInfo($orderProductHC);
            $Oproduct['qty']=1;
        }
        else{
            $Oproduct=getOrderProduct($orderProductHC);
            $product=getProduct($Oproduct['r_Product']);
            $shipInfo=getShipInfo($product['HC']); 
        }
        $Url = join("&", array("http://www.ups.com/using/services/rave/qcostcgi.cgi?accept_UPS_license_agreement=yes",
                    "10_action=3",
                    "13_product=".$shipInfo['productCode'],
                    "14_origCountry="."US",
                    "15_origPostal=".$shipInfo['shipFromZip'],
                    "19_destPostal=".$zip,
                    "22_destCountry="."US",
                    "23_weight=".($shipInfo['shipping_weight']*$Oproduct['qty']),
                    "47_rate_chart="."OP_WEB",
                    "48_container=".$shipInfo['package'],
                    "49_residential="."01"));
        $get=file_get_contents($Url);
        $result=explode("%", $get);
        return $result[8];
    }
    
    //obtains and returns tracking information from UPS
    function trackUPS($trackingNumber){
        $Url = join("&", array("http://www.ups.com/using/services/rave/qcostcgi.cgi?accept_UPS_license_agreement=yes",
                    "tracking=".$trackingNumber));
        $get=file_get_contents($Url);
        $result=explode("%", $get);
        return $result[8];
    }
    
    //obtains and returns tracking information from FEDEX
    function trackFEDEX($trackingNumber){
        $Url = join("&", array("http://www.fedex.com/using/services/rave/qcostcgi.cgi?accept_UPS_license_agreement=yes",
                    "tracking=".$trackingNumber));
        $get=file_get_contents($Url);
        $result=explode("%", $get);
        return $result[8];
    }
    
    //obtains and returns tracking information from USPS
    function trackUSPS(){
        //NYI
    }
    
    //obtains and returns FEDEX shipping cost within the given parameters.
    //$flag is 0 by default, if not then $orderProductHC is $ProductHC
    //$zip is destination zip code.
    function getFedExCost($orderProductHC, $zip, $flag=0, $optionArray=NULL){
        $product=getOrderProduct($orderProductHC);
        $product=getProduct($product['r_Product']);
        $shipInfo=getShipInfo($product['HC']);
        $xmlrequest='<?xml version="1.0" encoding="UTF-8" ?>
                    <FDXRateRequest xmlns:api="http://www.fedex.com/fsmapi" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="FDXRateRequest.xsd">
                    <RequestHeader> <CustomerTransactionIdentifier>CTIString</CustomerTransactionIdentifier>
                    <AccountNumber>510087941</AccountNumber> <MeterNumber>100038962</MeterNumber> <CarrierCode>FDXE</CarrierCode>
                    </RequestHeader> <ShipDate>2010-11-2</ShipDate> <DropoffType>REGULARPICKUP</DropoffType> <Service>PRIORITYOVERNIGHT</Service> <Packaging>FEDEXBOX</Packaging> <WeightUnits>LBS</WeightUnits> <Weight>'.$shipInfo['shipping_weight'].'</Weight> <OriginAddress>
                    <PostalCode>'.$shipInfo['shipFromZip'].'</PostalCode> <CountryCode>US</CountryCode>
                    </OriginAddress> <DestinationAddress>
                    <PostalCode>'.$zip.'</PostalCode> <CountryCode>US</CountryCode>
                    </DestinationAddress>
                    <Payment> <PayorType>SENDER</PayorType>
                    </Payment> <PackageCount>1</PackageCount> </FDXRateRequest>';
        $response=file_get_contents("http://fedex.com/");
        return "Invalid Information";
    }
    
    //obtains and returns USPS shipping cost within the given parameters.
    //$flag is 0 by default, if not then $orderProductHC is $ProductHC
    //$zip is destination zip code.
    function getUSPSCost($orderProductHC, $zip, $flag=0, $optionArray=NULL){
        $orderProduct=getOrderProduct($orderProductHC);
        $product=getProduct($orderProduct['r_Product']);
        $shipInfo=getShipInfo($product['HC']);
        $shipPounds=$shipInfo['shipping_weight'];
        $shipOunces=$shipPounds%1;
        $shipOunces=$shipOunces*16;
        $shipPounds=$shipPounds/1;
        $response=file_get_contents('http://production.shippingapis.com/ShippingAPI.dll?API=RateV3&XML=<?xml version="1.0" encoding="UTF-8" ?>
                           <RateV3Request USERID="'.$USPSID.'"><Package ID="0">
                           <Service>PRIORITY</Service>
                           <ZipOrigination>'.$shipInfo['shipFromZip'].'</ZipOrigination>
                           <ZipDestination>'.$zip.'</ZipDestination>
                           <Pounds>'.$shipPounds.'</Pounds>
                           <Ounces>'.$shipOunces.'</Ounces>
                           <Size>REGULAR</Size>
                           <Container />
                           <Machinable>true</Machinable>
                           </Package>
                           </RateV3Request>');
        //parse $response
        $cost=0;
        return $cost;
    }
    
    //For use with authorize.net response.
    function transact($response){
        
    }
    //returns an array of carriers, packaging information, shipping weight, shipFrom, and other information.
    function getShipInfo($productHC){
        $product=getProduct($productHC);
        $shippers=explode("|", $product['carriers']);
        $shipInfo['carriers']=$shippers;
        $shipInfo['package']=$product['package'];
        $shipInfo['shipping_weight']=$product['shipping_weight_lbs'];
        $shipInfo['productCode']=$product['shipProductCode'];
        $shipInfo['shipFromZip']=$product['shipFromZip'];
        $shipInfo['Flat']=$product['FlatRate'];
        return $shipInfo;
    }
?>