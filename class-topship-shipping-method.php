<?php
require_once plugin_dir_path(__FILE__) . 'includes/shipping_rate.php';
require_once plugin_dir_path(__FILE__) . 'includes/class.topship-db-init-service-africa.php';

if (!class_exists('Topship_Shipping_Method')) {
    class Topship_Shipping_Method extends WC_Shipping_Method {
        public function __construct() {
            $this->id                 = 'topship_last_mile'; // Unique ID for the shipping method
            $this->method_title       = __('Topship Last Mile Delivery', 'woocommerce');
            $this->method_description = __('Delivery service across Africa using Topship.', 'woocommerce');

            $this->enabled  = 'yes'; // Enable the method by default
            $this->title    = __('Topship Delivery', 'woocommerce'); // Title shown to users

            $this->init();
        }

        public function init() {
            require_once plugin_dir_path(__FILE__) . 'includes/class.topship-db-init-service-africa.php';
            Topship_Registration_Table::init();
            // Load the settings
            $this->init_form_fields();
            $this->init_settings();

            $this->enabled = $this->get_option('enabled');
            $this->title   = $this->get_option('title');

            // Save settings
            add_action('woocommerce_update_options_shipping_' . $this->id, [$this, 'process_admin_options']);
        }

        public function init_form_fields() {
            $this->form_fields = [
                'enabled' => [
                    'title'       => __('Enable/Disable', 'woocommerce'),
                    'type'        => 'checkbox',
                    'label'       => __('Enable this shipping method', 'woocommerce'),
                    'default'     => 'yes',
                ],
                'title' => [
                    'title'       => __('Method Title', 'woocommerce'),
                    'type'        => 'text',
                    'description' => __('This controls the title which the user sees during checkout.', 'woocommerce'),
                    'default'     => __('Topship Delivery', 'woocommerce'),
                    'desc_tip'    => true,
                ],
            ];
        }

        public function calculate_shipping($package = []) {

            // Get checkout session ID (example implementation)
            $checkout_session_id = WC()->session->get('order_awaiting_payment'); // WooCommerce session data
            if (!$checkout_session_id) {
                $checkout_session_id = uniqid('checkout_', true); // Generate a unique fallback ID if not available
                WC()->session->set('order_awaiting_payment', $checkout_session_id);
            }

            error_log('Checkout Session ID: ' . $checkout_session_id);

            // Define multiple shipping options
            // Access user information from the package
            $user_country  = isset($package['destination']['country']) ? $package['destination']['country'] : '';
            $user_state    = isset($package['destination']['state']) ? $package['destination']['state'] : '';
            $user_postcode = isset($package['destination']['postcode']) ? $package['destination']['postcode'] : '';
            $user_city     = isset($package['destination']['city']) ? $package['destination']['city'] : '';

            // Log or use the user information
            error_log("User Country: $user_country");
            error_log("User State: $user_state");
            error_log("User Postcode: $user_postcode");
            error_log("User City: $user_city");

            error_log("package: ". json_encode($package));

            $reg=  Topship_Registration_Table::get_user_record();

            error_log("reg: ". json_encode($reg));




            $resq='{
              "shipmentDetail": {
                "senderDetails": {
                  "cityName":' . ' "' . $reg['city'] . '",
                  "countryCode": "' . $reg['country_code'] . '"
                },
                "receiverDetails": {
                  "cityName":"' . $user_city. '",
                  "countryCode": "' . $user_country . '"
                },
                "totalWeight": 1
              }
            }';
            $data = json_decode( $resq);

            $res=  Class_topship_helper::getShipmentRate($data->shipmentDetail);

            //error_log("payload: ". json_encode( $res));


            if($res){
                //dd($res[0]);
                //$res= json_decode($res);
                $rates = [];
                foreach($res as $key => $method){
                    if($method['pricingTier']=='LastMileBudget')
                    {
                        if($data->shipmentDetail->totalWeight/1000>=10){
                            $newMethod=Class_topship_helper::getNameDescription($method);
                            $newPrice =$method['cost'];
                            $code =$checkout_session_id. $method['pricingTier'];//ValueAddedTaxes_Table::generate_unique_code($method['pricingTier']);
                            //Log::info('currency: '.$currency);
                            $valueAddedTax = ceil(Class_topship_helper::value_Added_Tax_Charge($newPrice));
                            $totalPrice=$newPrice+$valueAddedTax;
                            ValueAddedTaxes_Table::createValueAddedTax($valueAddedTax,2000,'',$method['cost'],'',$method['pricingTier'],'',$code);
                            $rates[]= [
                                'id' => $code,
                                'label' =>__($newMethod['mode'], 'woocommerce') ,// 'Top Ship',
                                'cost' => $totalPrice, // 50.00 in the currency
                                'description' =>__($newMethod['duration'], 'woocommerce'),
                            ];
                        }
                    }else{

                        $newMethod=Class_topship_helper::getNameDescription($method);
                        $newPrice =$method['cost'];
                        $code =$checkout_session_id. $method['pricingTier'];
                        //Log::info('currency: '.$currency);
                        $valueAddedTax = ceil(Class_topship_helper::value_Added_Tax_Charge($newPrice));
                        $totalPrice=$newPrice+$valueAddedTax;
                        ValueAddedTaxes_Table::createValueAddedTax($valueAddedTax,2000,'',$method['cost'],'',$method['pricingTier'],'',$code);
                        $rates[]= [
                            'id' => $code,
                            'label' => __($newMethod['mode'], 'woocommerce') ,// 'Top Ship',
                            'cost' =>$totalPrice,// $totalPrice/1000, // 50.00 in the currency
                            'description' => __($newMethod['duration'], 'woocommerce'),
                        ];

                    }
                }
                foreach ($rates as $option) {
                    $rate = [
                        'id'    => $option['id'],
                        'label' => $option['label'],
                        'cost'  => $option['cost'],
                        // Optionally include meta data
                        'meta_data' => [
                            'description' => $option['description'],
                        ],
                    ];
                    error_log('rate: '.json_encode($rate));
                    $this->add_rate($rate);
                }
                //Log::info('rate',$rates);
                // return response()->json(['rates' => $rates]);
            }else {

                $shipping_options = [
                    [
                        'id' => $this->id . '_standard',
                        'label' => __('Topship Standard Delivery', 'woocommerce'),
                        'cost' => 20.00, // Standard delivery cost
                        'description' => __('Delivery within 3-5 business days.', 'woocommerce'), // Optional
                    ],
                    [
                        'id' => $this->id . '_express',
                        'label' => __('Topship Express Delivery', 'woocommerce'),
                        'cost' => 50.00, // Express delivery cost
                        'description' => __('Delivery within 1-2 business days.', 'woocommerce'), // Optional
                    ],
                    [
                        'id' => $this->id . '_same_day',
                        'label' => __('Topship Same Day Delivery', 'woocommerce'),
                        'cost' => 100.00, // Same-day delivery cost
                        'description' => __('Delivery within the same day.', 'woocommerce'), // Optional
                    ],
                ];

                // Add each shipping option to the checkout
                foreach ($shipping_options as $option) {
                    $rate = [
                        'id' => $option['id'],
                        'label' => $option['label'],
                        'cost' => $option['cost'],
                        // Optionally include meta data
                        'meta_data' => [
                            'description' => $option['description'],
                        ],
                    ];
                    $this->add_rate($rate);
                }
            }
        }
    }
}
