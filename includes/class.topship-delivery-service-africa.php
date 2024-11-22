<?php 

require_once plugin_dir_path(__FILE__) . '/class.topship-helper.php';
class Class_topship_delivery_service_africa{

    public static function topshipLink(){
        return 'topship-africa-admin-page-01-ba5e0604-954d-4d49-b43e-61ac97f3eb75';
    }

    private static function render_navigation() {
        ?>
        <nav class="nav-tab-wrapper">
          <a href="<?php echo admin_url('admin.php?page='. self::topshipLink() ); ?>" class="nav-tab">Dashboard</a>
            <a href="<?php echo admin_url('admin.php?page='. self::topshipLink()  ); ?>-register" class="nav-tab">Create account</a>
            <a href="<?php echo admin_url('admin.php?page='. self::topshipLink()  ); ?>-contact-us" class="nav-tab">Contact Us</a>
            <a href="<?php echo admin_url('admin.php?page='. self::topshipLink()  ); ?>-guide" class="nav-tab">Guide</a>
        </nav>
        <?php
    }

     public static function topship_dashboard() {
        ?>
         <div class="container">
             <div class="row">
                 <div class="col-md-12 mx-auto p-4">
                     <?php self::render_navigation(); ?>
                     <?php
                     wp_enqueue_style('uptown-css', plugins_url('../css/style.css', __FILE__));
                     wp_enqueue_script('my-plugin-js', plugin_dir_url(__FILE__) . '../js/my-plugin.js', ['vue-js'], null, true);
                     ?>
                     <div class="shadow bg-white p-5">
                         <div id="app">
                             <order-component></order-component>
                         </div>
                     </div>
                 </div>
             </div>
         </div>
         <script>
             document.addEventListener('DOMContentLoaded', function () {
                 // Define the Vue app
                 var app = Vue.createApp({
                     data() {
                         return {
                             orders: [],
                             selectedOrder: null,
                             load: {
                                 fetchingOrders: false,
                                 orderDetails: false
                             },
                             message: { title: '', message: '', show: false },
                             show: false
                         };
                     },
                     methods: {
                         async fetchOrders() {
                             this.load.fetchingOrders = true;
                             try {
                                 const response = await fetch('<?php echo esc_url(home_url('/wp-json/topship/v1/orders')); ?>');
                                 if (!response.ok) throw new Error('Failed to fetch orders');
                                 this.orders = await response.json();
                             } catch (error) {
                                 this.showMessage('Error', 'Failed to fetch orders. Please try again later.');
                             } finally {
                                 this.load.fetchingOrders = false;
                             }
                         },
                         async viewOrderDetails(orderId) {
                             this.load.orderDetails = true;
                             try {
                                 const response = await fetch(`<?php echo esc_url(home_url('/wp-json/topship/v1/orders/')); ?>${orderId}`);
                                 if (!response.ok) throw new Error('Failed to fetch order details');
                                 this.selectedOrder = await response.json();
                             } catch (error) {
                                 this.showMessage('Error', 'Failed to fetch order details. Please try again later.');
                             } finally {
                                 this.load.orderDetails = false;
                             }
                         },
                         showMessage(title, message) {
                             this.message.title = title;
                             this.message.message = message;
                             this.message.show = true;
                         },
                         hideMessage() {
                             this.message.show = false;
                         }
                     },
                     mounted() {
                         this.fetchOrders();
                     }
                 });

                 // Define the OrderComponent
                 app.component('order-component', {
                     template: `
            <div>
                <h1>Your Orders</h1>
                <div v-if="load.fetchingOrders" class="loading-spinner">Loading...</div>
                <ul v-else>
                    <li v-for="order in orders" :key="order.id">
                        <button @click="viewOrderDetails(order.id)">
                            Order #{{ order.id }} - {{ order.date }}
                        </button>
                    </li>
                </ul>

                <div v-if="selectedOrder">
                    <h2>Order Details</h2>
                    <p>Order ID: {{ selectedOrder.id }}</p>
                    <p>Date: {{ selectedOrder.date }}</p>
                    <p>Status: {{ selectedOrder.status }}</p>
                    <ul>
                        <li v-for="item in selectedOrder.items" :key="item.id">
                            {{ item.name }} - {{ item.quantity }}
                        </li>
                    </ul>
                </div>

                <mess :title="message.title" :message="message.message" :show="message.show" @closed="hideMessage" />
            </div>
        `,
                     data() {
                         return {
                             orders: [],
                             selectedOrder: null,
                             load: {
                                 fetchingOrders: false,
                                 orderDetails: false
                             },
                             message: { title: '', message: '', show: false }
                         };
                     },
                     methods: {
                         async fetchOrders() {
                             this.load.fetchingOrders = true;
                             try {
                                 const response = await fetch('<?php echo esc_url(home_url('/wp-json/topship/v1/orders')); ?>');
                                 if (!response.ok) throw new Error('Failed to fetch orders');
                                 this.orders = await response.json();
                             } catch (error) {
                                 this.showMessage('Error', 'Failed to fetch orders. Please try again later.');
                             } finally {
                                 this.load.fetchingOrders = false;
                             }
                         },
                         async viewOrderDetails(orderId) {
                             this.load.orderDetails = true;
                             try {
                                 const response = await fetch(`<?php echo esc_url(home_url('/wp-json/topship/v1/orders/')); ?>${orderId}`);
                                 if (!response.ok) throw new Error('Failed to fetch order details');
                                 this.selectedOrder = await response.json();
                             } catch (error) {
                                 this.showMessage('Error', 'Failed to fetch order details. Please try again later.');
                             } finally {
                                 this.load.orderDetails = false;
                             }
                         },
                         showMessage(title, message) {
                             this.message.title = title;
                             this.message.message = message;
                             this.message.show = true;
                         },
                         hideMessage() {
                             this.message.show = false;
                         }
                     },
                     mounted() {
                         this.fetchOrders();
                     }
                 });

                 // Mount the app
                 app.mount('#app');
             });
         </script>

         <?php
     }

    public static function topship_register() { ?>
        <div class="container">
            <div class="row">
                <div class="col-md-12 mx-auto p-4">
                    <?php self::render_navigation(); ?>
                    <?php
                    wp_enqueue_style('uptown-css', plugins_url('../css/style.css', __FILE__));
                    wp_enqueue_script('my-plugin-js', plugin_dir_url(__FILE__) . '../js/my-plugin.js', ['vue-js'], null, true);
                    ?>
                    <div class="shadow bg-white p-5">
                    <div id="app">
                        <registration-component></registration-component>
                    </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            var pagetoken ='';
            function onSubmit(token) {
                pagetoken=token;
            }
            document.addEventListener('DOMContentLoaded', function () {
                // Define the Vue app
                var app = Vue.createApp({
                    data() {
                        return {
                            show: false,
                            title: '',
                            message: ''
                        };
                    },
                    methods: {
                        showDialog(title, message) {
                            this.title = title;
                            this.message = message;
                            this.show = true;
                        },
                        hideDialog() {
                            this.show = false;
                        }
                    }
                });

              /*  // Define the DialogMessage component with scoped CSS
                app.component('DialogMessage', {
                    template: `
                <div v-if="show" id="dialogMessageModal" class="modal fade show" tabindex="-1" role="dialog" aria-labelledby="dialogMessageModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">{{ title }}</h5>
                                <button type="button" class="close" @click="hideDialog" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <p v-html="message"></p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-primary" @click="hideDialog">OK</button>
                            </div>
                        </div>
                    </div>
                </div>
            `,
                    data() {
                        return {
                            show: false,
                            title: '',
                            message: ''
                        };
                    },
                    methods: {
                        showDialog(title, message) {
                            this.title = title;
                            this.message = message;
                            this.show = true;
                        },
                        hideDialog() {
                            this.show = false;
                        }
                    }
                });
            */
                app= utilityVueComponent(app);
                // Define the RegistrationComponent
                app.component('registration-component', {
                    template: `

                <div style="">
                    <div class="row" style="">
                        <div class="col-md-5">
                            <div style="text-align: start; width: 80%">
                                <h1 style="font-weight: bold">Register your shop</h1>
                                <p style="text-align: start">Already a Topship user? Please use the same information on your
                                    existing Topship account.</p>
                            </div>
                            <div class="imgdev">
                                <img class="rotate-90" src="<?php echo  plugin_dir_url(__FILE__) . '../image/path.png'?>" alt="image"/>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <form @submit.prevent="registration" class="form form-container custom-form" id="reg">
                                <div class="row my-3">
                                    <div class="form-group col-md-6 col-lg-6 col-sm-12">
                                        <label for="firstName" class="mb-0">First Name *</label>
                                        <input type="text" class="form-control" v-model="data.firstName" id="firstName" placeholder="Stark" required>
                                    </div>
                                    <div class="form-group col-md-6 col-lg-6 col-sm-12">
                                        <label for="lastName" class="mb-0">Last Name *</label>
                                        <input type="text" class="form-control" v-model="data.lastName" id="lastName" placeholder="Stark" required>
                                    </div>
                                </div>
                                <div class="form-group my-3">
                                    <label for="phone" class="mb-0">Phone Number *</label>
                                    <input type="tel" class="form-control" v-model="data.phoneNumber" id="phone" placeholder="Enter phone number" required>
                                </div>
                                <div class="form-group my-3">
                                    <label for="email" class="mb-0">Email Address *</label>
                                    <input type="email" class="form-control" v-model="data.email" id="email" placeholder="Enter email address" required>
                                </div>
                                <div class="form-group">
                                    <label for="address" class="mb-0">Address *</label>
                                    <input type="text" class="form-control" v-model="data.address" id="address" placeholder="Enter address" required>
                                </div>
                                <div class="row my-3">
                                    <div class="form-group">
                                        <label for="country" class="mb-0">Country *  <loader :loading="load.country"/></label>
                                        <select class="form-control" v-model="country" @change="countrySelect" id="country" required>
                                            <option value="">Select Country </option>
                                            <option v-for="country in countries" :key="country.id" :value="country.code">
                                                {{ country.name }}
                                            </option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-6 my-3">
                                        <label for="state" class="mb-0">State * <loader :loading="load.state"/> </label>
                                        <select class="form-control" v-model="stateSelected" @change="getCities" id="state" required>
                                            <option v-for="(item,index) in stateList" :value="item.name" :key="index">{{item.name}}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row my-3">
                                    <div class="form-group col-md-6">
                                        <label for="city" class="mb-0">City * <loader :loading="load.city"/></label>
                                        <select class="form-control" v-model="citySelected" id="city" required>
                                            <option v-for="(item,index) in cityList" :value="item.cityName" :key="index">{{item.cityName}}</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-6">
                                         <label class="mb-0" for="zipcode">Postal Code</label>
                                         <input v-model="data.zipcode" type="text"  id="zipcode" name="zipcode" required class="form-control">
                                    </div>
                                </div>

                                <div class="form-group my-3">
                                    <label for="password" class="mb-0">Password *</label>
                                    <input type="password" class="form-control" v-model="data.password" id="password" placeholder="Enter password" required>
                                </div>

                <div class="mt-3" style="text-align: center">
                <div id="recaptcha-container" class="g-recaptcha center" style="margin-top: 0rem;" data-sitekey="6Leu53UnAAAAAAJj75YNK0bBMD-3v1lS8oQN8fi7" data-callback="onSubmit"></div>

<div class="secondary center" style="padding-left: 5rem;padding-right: 5rem;padding-top: 2rem;">
                        <p class="mt-3" style="text-align: center;">
                            By clicking Register, I acknowledge that I have read, understand and agree to the Topship’s <a href="https://ship.topship.africa/terms">Privacy Policy</a> and <a href="https://ship.topship.africa/terms">Terms of Service</a>
                        </p>
                    </div>

                                <button type="submit" class="btn btn-primary mt-3">
                                    <span v-if="load.register">Loading...</span>
                                    <span v-else>Register</span>
                                </button>
                                </div>

                        <dial :show="show"/>
                      <mess :title="message.title" :message="message.message" :show="message.show"  @closed="closed"/>

                            </form>
                          </div>
                    </div>
                </div>
            `,
                    data() {
                        return {
                            base_url: 'https://example.com/',
                            data: {
                                firstName: '',
                                lastName: '',
                                phoneNumber: '',
                                email: '',
                                address: '',
                                zipcode: '',
                                password: '',
                                show:false,
                            },
                            country: '',
                            stateSelected: '',
                            citySelected: '',
                            countries: [],
                            stateList: [],
                            cityList: [],
                            load: {
                                register: false,
                                state: false,
                                city: false,
                                country:false,
                            },
                            message:{title:'My title',message:'my message to you',show:false},
                            show:false,
                            recache:false,
                        };
                    },
                    methods: {
                        async onClick() {
                            this.google_token= pagetoken;
                        },
                       /* async registration() {
                            this.load.register = true;
                            try {
                                const response = await fetch(`${this.base_url}api/register`, {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify(this.data)
                                });
                                const result = await response.json();
                                if (response.ok) {
                                    this.$emit('showDialog', 'Registration Successful', 'Your account has been registered successfully.');
                                } else {
                                    throw new Error(result.message || 'Registration failed.');
                                }
                            } catch (error) {
                                this.$emit('showDialog', 'Error', error.message);
                            } finally {
                                this.load.register = false;
                            }
                        },*/
                        async registration() {
                           // this.show=true;

                            await this.onClick();
                            if(this.google_token.length>0){
                                this.data.recaptchaToken=this.google_token;
                            }
                            else{
                                this.showMessage('Error','check recaptchaToken')
                                return;
                            }
                            //alert(this.google_token);
                            this.data.fullName = this.data.firstName +" "+ this.data.lastName;
                            this.data.id=this.userid;
                            this.load.register = true
                            this.data.country_code = this.country;
                            this.data.state = this.stateSelected;
                            this.data.city = this.citySelected;
                            this.data.country = this.countries.find(c => c.code === this.data.country_code).name;
                            const headers = {
                                'Authorization': `Bearer ${this.token}`
                            };

                            this.show=true;
                            axios.post(`<?php echo esc_url(home_url('/wp-json/topship/v1/register')); ?>`, this.data, {headers})
                                .then(response => {
                                    //Handle successful registration
                                    console.log('Registration successful:', response.data);
                                    this.load.register = false;
                                    this.show=false;
                                    this.load.register = false;
                                    this.toast('Registration successful','green')
                                    this.reloadPage();
                                })
                                .catch(error => {
                                    //Handle registration errors
                                    this.show=false;
                                    this.load.register = false;
                                    if (error.code === 'ECONNABORTED') {
                                        // console.error('Connection timeout:', error.message);
                                        this.showMessage('Error', 'Connection timeout. Please try again.');
                                    } else if (error.response) {
                                        // Handle other registration errors
                                        //console.error('Registration error:', error.response.data);
                                        this.showMessage('Error', error.response.data.message);
                                    } else {
                                        //console.error('Unexpected error:', error.message);
                                        this.showMessage('Error', 'Connection timeout. Please try again.');
                                    }
                                });
                        },
                        async countrySelect() {
                           //this.showMessage('Error','check recaptchaToken');
                           // this.toast('Registration successful','green')
                           // this.message.show=true;
                            //this.reloadPage();
                            // return;

                            this.load.state = true;
                            try {
                                const response = await fetch(`<?php echo esc_url(home_url('/wp-json/topship/v1/states/')); ?>${this.country}`);
                                if (!response.ok) throw new Error('Failed to fetch states');
                                this.stateList = await response.json();
                            } catch (error) {
                                console.error('Failed to fetch states:', error);
                            } finally {
                                this.load.state = false;
                            }
                        },
                        async getCities() {
                            this.load.city = true;
                            try {
                                const url = `<?php echo esc_url(home_url('/wp-json/topship/v1/cities/')); ?>${this.country}`;
                                const response = await fetch(url);
                                this.cityList = await response.json();
                            } catch (error) {
                                console.error('Failed to fetch cities:', error);
                            } finally {
                                this.load.city = false;
                            }
                        },
                        reloadPage() {
                            location.reload();
                        },
                        showMessage(title,message){
                            this.message.title=title;
                            this.message.message=message
                            this.message.show=true;
                        },
                        open_message_test(){
                            this.message.show=true;
                        },
                        closed(action){
                            this.message.show=false;
                        }
                        ,
                        toast(text,bgColor){
                            Toastify({
                                text: text,
                                className: "info",
                                style: {
                                    background: bgColor,
                                }
                            }).showToast();
                        },
                    },
                    mounted() {
                        this.load.country = true;
                        fetch(`<?php echo esc_url(home_url('/wp-json/topship/v1/countries')); ?>`)
                            .then(response => response.json())
                            .then(data => this.countries = data)
                            .catch(error => console.error('Failed to fetch countries:', error));
                        this.load.country = false;

                        var recaptchaWidgetId;
                       //console.log(this.countries)
                        try {
                            recaptchaWidgetId = grecaptcha.render('recaptcha-container', {
                                'sitekey': '6Leu53UnAAAAAAJj75YNK0bBMD-3v1lS8oQN8fi7',
                                'callback': onSubmit
                            });
                        }catch (e) {
                            
                        }
                    }
                });
                app.mount('#app');
            });
        </script>
<?php
        }



public static function topship_guide_page(){
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>

<div class="container">

<div class="row">
<div class="col-md-10 mx-auto p-4">
<?php self::render_navigation()  ?>
<div class="shadow bg-white p-5">

<h2 class="mt-5fw-bold mb-5">Contact Us</h2>
<form>
<div class="form-group my-3">
<label for="fullName">Full Name *</label>
<input type="text" class="form-control" id="fullName" placeholder="Enter full name" required>
</div>
<div class="form-group my-3">
<label for="phone">Phone Number *</label>
<input type="tel" class="form-control" id="phone" placeholder="Enter phone number"  required>
</div>
<div class="form-group my-3">
<label for="email">Email Address *</label>
<input type="email" class="form-control" id="email" placeholder="Enter email address"  required>
</div>
<div class="form-group my-3">
<label for="businessName">Business Name *</label>
<input type="text" class="form-control" id="businessName" placeholder="Enter business name" required>
</div>
<div class="form-group my-3">
<label for="businessWebsite">Business Website (optional)</label>
<input type="url" class="form-control" id="businessWebsite" placeholder="Enter business website">
</div>
<div class="form-group my-3">
<label for="message">Your Message *</label>
<textarea class="form-control" id="message" rows="3" placeholder="Enter your message" required></textarea>
</div>
<button type="submit" class="btn btn-primary w-100">Send Message</button>
</form>
            
            
           
       
</div>
</div>

</div>

</div>
    <?php
}









public static function topship_contact_us_page(){

        if (!current_user_can('manage_options')) {
            return;
        }
        ?>
    
   <div class="container">

  <div class="row">
  <div class="col-md-10 mx-auto p-4">
  <?php self::render_navigation()  ?>
  <div class="shadow bg-white p-5">


  <h2 class="mt-5fw-bold mb-5">Contact Us</h2>
  <form>
  <div class="form-group my-3">
    <label for="fullName">Full Name *</label>
    <input type="text" class="form-control" id="fullName" placeholder="Enter full name" required>
  </div>
  <div class="form-group my-3">
    <label for="phone">Phone Number *</label>
    <input type="tel" class="form-control" id="phone" placeholder="Enter phone number"  required>
  </div>
  <div class="form-group my-3">
    <label for="email">Email Address *</label>
    <input type="email" class="form-control" id="email" placeholder="Enter email address"  required>
  </div>
  <div class="form-group my-3">
    <label for="businessName">Business Name *</label>
    <input type="text" class="form-control" id="businessName" placeholder="Enter business name" required>
  </div>
  <div class="form-group my-3">
    <label for="businessWebsite">Business Website (optional)</label>
    <input type="url" class="form-control" id="businessWebsite" placeholder="Enter business website">
  </div>
  <div class="form-group my-3">
    <label for="message">Your Message *</label>
    <textarea class="form-control" id="message" rows="3" placeholder="Enter your message" required></textarea>
  </div>
  <button type="submit" class="btn btn-primary w-100">Send Message</button>
</form>
                
                
               
           
    </div>
  </div>

  </div>

   </div>
        <?php
    }







}
