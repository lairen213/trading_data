function curl($url, $post = null, $head=0){
$ch = curl_init($url);
curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 60);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

if($head){
curl_setopt($ch,CURLOPT_HTTPHEADER, $head);
}else{
curl_setopt($ch,CURLOPT_HEADER, 0);
}

if($post){
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
}
$response = curl_exec($ch);
$header_data = curl_getinfo($ch);
curl_close($ch);
return $response;
}

function createNewOrder($order_id){
$order = wc_get_order( $order_id );
if($order){
$total = 0;
// Iterating through each WC_Order_Item_Product objects
foreach ($order->get_items() as $item_key => $item ){
$total += $item->get_total(); // Line total (discounted)
}

$order_arr = [];
$order_arr['token'] = 'YOUR_TOKEN';
$order_arr['click'] = $_SESSION['client_id'];
$order_arr['price'] = $total;

$result = curl('https://domain.com/api/site/click.json?'.http_build_query($order_arr));
}
}

function changeStatusOfOrder($status){
$order_arr = [];
$order_arr['token'] = 'YOUR_TOKEN';
$order_arr['click'] = $_SESSION['client_id'];
$order_arr['status'] = $status;

$order_arr['sta'] = 'completed';
$order_arr['stc'] = 'cancelled';
$order_arr['stt'] = 'failed';
$order_arr['stw'] = 'processing';

$result = curl('https://domain.com/api/site/status.json?'.http_build_query($order_arr));
}


function register_session_new(){
if( ! session_id() ) {
session_start();
}

if( isset( $_GET['client_id'] ) ) {
$_SESSION['client_id']=$_GET['client_id'];
}
if(isset( $_GET['pixel_id'])){
$_SESSION['pixel_id'] = $_GET['pixel_id'];
}

}

add_action('init', 'register_session_new');



//NEW ORDER
function create_invoice_for_wc_order( $order_id ) {
createNewOrder($order_id);
}

add_action( 'woocommerce_new_order', 'create_invoice_for_wc_order',  1, 1);


//CHANGE ORDER STATUS
function mysite_pending($order_id) {
echo '<h1>Pending</h1>';
changeStatusOfOrder('processing');
}
function mysite_failed($order_id) {
echo '<h1>Failed</h1>';
changeStatusOfOrder('failed');
}
function mysite_processing($order_id) {
echo '<h1>Processing</h1>';
changeStatusOfOrder('processing');
}
function mysite_completed($order_id) {
echo '<h1>Completed</h1>';
changeStatusOfOrder('completed');
}
function mysite_refunded($order_id) {
echo '<h1>Refunded</h1>';
changeStatusOfOrder('cancelled');
}
function mysite_cancelled($order_id) {
echo '<h1>Cancelled</h1>';
changeStatusOfOrder('cancelled');
}

add_action( 'woocommerce_order_status_pending', 'mysite_pending', 10, 1);
add_action( 'woocommerce_order_status_failed', 'mysite_failed', 10, 1);
add_action( 'woocommerce_order_status_processing', 'mysite_processing', 10, 1);
add_action( 'woocommerce_order_status_completed', 'mysite_completed', 10, 1);
add_action( 'woocommerce_order_status_refunded', 'mysite_refunded', 10, 1);
add_action( 'woocommerce_order_status_cancelled', 'mysite_cancelled', 10, 1);


add_action( 'template_redirect', 'add_facebook_pixel_on_thankyou_page' );

function add_facebook_pixel_on_thankyou_page(){

// do nothing if we are not on the order received page
if( ! is_wc_endpoint_url( 'order-received' ) || empty( $_GET[ 'key' ] ) ) {
return;
}

add_action('wp_head', 'add_facebook_pixel_on_thankyou_page_2');
return;
}

function add_facebook_pixel_on_thankyou_page_2(){
if(isset($_SESSION['pixel_id']) && $_SESSION['pixel_id']){
echo '<img height="1" width="1" src="https://www.facebook.com/tr?id='.$_SESSION['pixel_id'].'&ev=Lead&noscript=1" referrerpolicy="no-referrer"/>';
}
}
