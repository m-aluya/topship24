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
            <a href="<?php echo admin_url('admin.php?page='. self::topshipLink()  ); ?>-pending" class="nav-tab">Pending Request</a>
            <a href="<?php echo admin_url('admin.php?page='. self::topshipLink()  ); ?>-contact-us" class="nav-tab">Contact Us</a>
            <a href="<?php echo admin_url('admin.php?page='. self::topshipLink()  ); ?>-guide" class="nav-tab">Guide</a>
        </nav>
        <?php
    }

    public static function topship_pending_request_page(){
        ?>
        <div class="container">
            <div class="">
                <div class="">
                    <?php self::render_navigation(); ?>
                    <?php
                    wp_enqueue_style('uptown-css', plugins_url('../css/style.css', __FILE__));
                    wp_enqueue_script('my-plugin-js', plugin_dir_url(__FILE__) . '../js/my-plugin.js', ['vue-js'], null, true);
                    ?>
                    <div class="shadow bg-white p-5">
                        <div id="app">
                               <pending-component></pending-component>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
            var app = Vue.createApp({
                data() {
                    return {
                        show: false,
                        title: '',
                        message: '',
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

            app= utilityVueComponent(app);

            app.component('pending-component',{
                template:`

<div class="container">
    <div class="">
        <div class="">
            <h1 class="card-title">Pending Requests</h1>
            <p class="card-text text-warning">These failed shipment requests will be retried automatically.</p>
            <div class="table-responsive">
            <table class="table table-bordered table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>Order ID</th>
                            <th>Topship Reason</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="shipment in failedShipments" :key="shipment.id">
                            <td>{{ shipment.order_id }}</td>
                            <td>
                                <td>{{ shipment.reason }}</td>

                            </td>
                            <td>Booking pending</td>
                        </tr>
                    </tbody>
                </table>
            </div>
<button @click="retry" class="btn btn-primary">Retry All</button>
        </div>
    </div>
 <dial :show="show"/>
            <mess :title="message.title" :message="message.message" :show="message.show"  @closed="closed"/>

</div>


                `,
                data() {
                    return {
                        failedShipments: [],
                        show: false,
                        message: { title: 'My title', message: 'my message to you', show: false },
                    };
                },
                created() {
                    this.fetchFailedShipments();
                },
                mounted() {
                    this.startAutoRefresh();
                }
                ,

                methods: {
                    retry(){
                        this.show=true;
                        fetch('<?php echo esc_url(home_url('/wp-json/topship/v1/retry')); ?>')
                            .then((response) => response.json())
                            .then((data) => {
                                this.show=false;
                            })
                            .catch((error) => {
                                this.show=false;
                            });
                        this.fetchFailedShipments();
                    },
                    showMessage(title, message) {
                        this.message.title = title;
                        this.message.message = message;
                        this.message.show = true;
                    },
                    closed(action) {
                        this.message.show = false;
                    },
                    fetchFailedShipments() {
                        this.show=true;
                        fetch('<?php echo esc_url(home_url('/wp-json/topship/v1/pending')); ?>')
                            .then((response) => response.json())
                            .then((data) => {
                                this.show=false;
                                this.failedShipments = data;
                                console.log('failedShipments',this.failedShipments);
                            })
                            .catch((error) => {
                                this.show=false;
                                console.error('Error fetching failed shipments:', error);
                            });
                    },
                    startAutoRefresh() {
                        this.refreshInterval = setInterval(() => {
                            this.fetchFailedShipments();
                        }, 100000);
                    },
                },
                unmounted() {
                    clearInterval(this.refreshInterval);
                }
                ,
                beforeDestroy() {
                    clearInterval(this.refreshInterval);
                }
            });
            app.mount('#app');
            });
        </script>
        <?php
    }

    public static function topship_dashboard() {
        ?>
        <div class="container">
            <div class="row">
                <div class="col-md-12 mx-auto p-4">
                    <?php self::render_navigation(); ?>
                    <?php
                    wp_enqueue_style('general-style', plugins_url('../css/style.css', __FILE__));
                    wp_enqueue_style('dashboard-style', plugins_url('../css/style_dashboard.css', __FILE__));
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

                        };
                    },
                    methods: {
                        async fetchOrders() {
                            this.load.fetchingOrders = true;
                            try {
                                const response = await fetch('<?php echo esc_url(rest_url('topship/v1/order')); ?>', {
                                    method: 'POST', // Ensure the method matches your API endpoint
                                    headers: {
                                        'Content-Type': 'application/json'
                                    },
                                    body: JSON.stringify({
                                        per_page: 10, // Adjust as needed
                                        page: 1       // Default to the first page
                                    })
                                });
                                if (!response.ok) throw new Error('Failed to fetch orders');
                                const data = await response.json();
                                this.orders = data.data; // Assuming 'data' contains the orders array
                            } catch (error) {
                                this.showMessage('Error', 'Failed to fetch orders. Please try again later.');
                            } finally {
                                this.load.fetchingOrders = false;
                            }
                        },
                        async viewOrderDetails(orderId) {
                            this.load.orderDetails = true;
                            try {
                                const response = await fetch(`<?php echo esc_url(rest_url('topship/v1/order/')); ?>${orderId}`);
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

                app= utilityVueComponent(app);

                // Define the OrderComponent
                app.component('order-component', {
                    template: `
        <div class="container">
            <div class="table-container">
                <h1 class="title">Shipment Dashboard</h1>
 <div class="table-responsive">
            <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Shipment ID</th>
                            <th>Tracking ID</th>
                            <th>Route</th>
                            <th>Status</th>
                            <th>Total Charge</th>
                            <th>Currency</th>
                            <th>Total Weight</th>
                            <th>Created Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="booking in shipmentBookings.data" :key="booking.id">
                            <td>{{ booking.shipment_id.substring(0, 10) }}</td>
                            <td>{{ booking.tracking_id }}</td>
                            <td>{{ booking.shipment_route }}</td>
                            <td>
                                <span :class="getStatusClass(booking.shipment_status)">
                                    {{ booking.shipment_status === 'Paid' ? 'Successful' : 'Pending' }}
                                </span>
                            </td>
                            <td>{{ formatCurrency(booking.total_charge) }}</td>
                            <td>{{ booking.currency }}</td>
                            <td>{{ booking.total_weight }}</td>
                            <td>{{ formatDateString(booking.created_date) }}</td>
                            <td>
                                <button class="btn btn-primary btn-sm px-3" v-if="booking.shipment_status === 'Draft'" @click="payForBooking(booking.id)">
                                    Pay Charge
                                </button>
                                <p v-else>No action required</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
</div>
                <p id="message"></p>
            </div>
            <div class="pagination">
                <div class="columns six" style="text-align: start">
                    <span>Showing {{ shipmentBookings.meta.from || 0 }} - {{ shipmentBookings.meta.to || 0 }} from {{ shipmentBookings.meta.total || 0 }}</span>
                </div>
                <div class="columns six" style="text-align: end">
                    <button @click="fetchShipmentBookings(shipmentBookings.meta.prev_page_url)" :disabled="!shipmentBookings.meta.prev_page_url">
                        <i class="fa fa-angle-left"></i>
                    </button>
                    &nbsp;
                    <button @click="fetchShipmentBookings(shipmentBookings.meta.next_page_url)" :disabled="!shipmentBookings.meta.next_page_url">
                        <i class="fa fa-angle-right"></i>
                    </button>
                </div>
            </div>
            <dial :show="show"/>
            <mess :title="message.title" :message="message.message" :show="message.show"  @closed="closed"/>

        </div>
    `,
                    data() {
                        return {
                            shipmentBookings: {
                                data: [],
                                meta: {
                                    total: 0,
                                    per_page: 10,
                                    current_page: 1,
                                    last_page: 1,
                                    prev_page_url: null,
                                    next_page_url: null
                                }
                            },
                            show: false,
                            message: { title: 'My title', message: 'my message to you', show: false },
                        };
                    },
                    methods: {
                        showMessage(title, message) {
                            this.message.title = title;
                            this.message.message = message;
                            this.message.show = true;
                        },
                        async fetchShipmentBookings(url = '<?php echo esc_url(rest_url('topship/v1/order')); ?>') {
                            try {
                                 this.show = true;
                                const response = await fetch(url, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json'
                                    },
                                    body: JSON.stringify({
                                        per_page: this.shipmentBookings.meta.per_page,
                                        page: this.shipmentBookings.meta.current_page
                                    })
                                });
                                if (!response.ok) throw new Error('Failed to fetch shipment bookings');
                                const data = await response.json();
                                this.shipmentBookings = data;
                                this.show = false;
                            } catch (error) {
                                console.error('Error fetching shipment bookings:', error);
                                this.show = false;
                            }
                        },
                        payForBooking(bookingId) {
                            console.log(`Pay for booking: ${bookingId}`);
                            // Implement payment logic here
                            const payload = {
                                bookingId: bookingId
                            };
                            this.show = true;
                            axios.post(`<?php echo esc_url(rest_url('topship/v1/payforbooking')); ?>`, payload)
                                .then(response => {
                                    this.fetchShipmentBookings();
                                })
                                .catch(error => {
                                    this.show = false;
                                    this.showMessage('Error', 'Payment Failed, Please ensure you have sufficient balance');
                                });
                        },
                        getStatusClass(status) {
                            return status === 'Paid' ? 'status-success' : 'status-pending';
                        },
                        formatCurrency(value) {
                            return (parseFloat(value) / 100).toFixed(2).toLocaleString();
                        },
                        formatDateString(dateString) {
                            const date = new Date(dateString);
                            return date.toLocaleDateString();
                        },
                        closed(action) {
                            this.message.show = false;
                        }
                    },
                    mounted() {
                        this.fetchShipmentBookings();
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
                            message: '',

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
                                    <div class="form-group col-md-6 my-3">
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
                                         <label class="mb-0" for="zipcode">Postal Code *</label>
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
            <div class="col-md-12 mx-auto p-4">
                <?php self::render_navigation(); ?>
                <?php
                wp_enqueue_style('uptown-css', plugins_url('../css/style.css', __FILE__));
                wp_enqueue_script('my-plugin-js', plugin_dir_url(__FILE__) . '../js/my-plugin.js', ['vue-js'], null, true);
                ?>
                <div class="shadow bg-white p-5">
                    <div>

                        <div style="">
                            <div class="row" style="">
                                <div class="col-md-5">
                                    <div style="text-align: start; width: 80%">
                                        <h1 class="fw-bold">Guide</h1>
                                        <p>
                                            Topship helps you send packages of all sizes to customers at any location worldwide.
                                            Available to businesses located in Nigeria only. For more information, visit
                                            <a href="https://www.topship.africa" target="_blank">www.topship.africa</a>
                                        </p>
                                    </div>
                                    <div class="imgdev">
                                        <img class="rotate-90" src="<?php echo  plugin_dir_url(__FILE__) . '../image/path.png'?>" alt="image"/>
                                    </div>
                                </div>
                                <div id="app" class="col-md-7">
                                    <guide-component></guide-component>
                                </div>
                            </div>
                        </div>


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
                        show: false,
                        title: '',
                        message: '',
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

            //app= utilityVueComponent(app);
            // Define the RegistrationComponent
            app.component('guide-component', {
                template: `
<div class="card border-0">
                <h4 class="text-start">Things to note for a stress-free plug-in experience</h4>
                <ul class="text-start">
                    <li>
                        Create a Topship account in the signup form, log in to your account, and credit your wallet to avoid any interruptions in your shipping experience.
                        <a href="https://ship.topship.africa/signup" target="_blank">Signup</a>
                    </li>
                    <li>
                        <p>Configure your checkout settings as follows:</p>
                        <span class="badge bg-warning text-dark">Phone number is required</span><br>
                        <span class="badge bg-warning text-dark">First name and last name are required</span>
                     </li>
   <img style="width:100%" class="" src="<?php echo  plugin_dir_url(__FILE__) . '../image/checkout.PNG'?>" alt="Checkout settings">

                    <li>
                        <p>All shipments booked via the plugin are “drop-off only.” You must drop the packages off at a Topship drop-off center. See the list of hubs:
                            <a href="https://topship.africa/#drop-off-hubs" target="_blank">Drop-off hubs</a>
                        </p>
                    </li>
                    <li>
                        <p>If you live in a city without a Topship hub, contact us for a custom solution. Email us at
                            <a href="mailto:hello@topship.africa" target="_blank">hello@topship.africa</a>.
                        </p>
                    </li>
                    <li>
                        <p>This plugin is available to businesses that ship out of Nigeria only.</p>
                    </li>
                </ul>
                <hr>
                <h4 class="text-start">Understanding our shipping rates:</h4>
                <p>We offer flexible prices to simplify shipping for your business and customers:</p>
                <ul class="text-start">
                    <li><span class="text-primary fw-bold">Express:</span> Delivery in 3 - 7 business days (import duties not included).</li>
                    <li><span class="text-primary fw-bold">Saver Priority:</span> Delivery in 5 - 7 business days (import duties not included).</li>
                    <li><span class="text-primary fw-bold">Saver:</span> Delivery in 10 - 12 business days (import duties not included).</li>
                    <li><span class="text-primary fw-bold">Budget:</span> Delivery in 10 - 15 business days (inclusive of import duties).</li>
                </ul>
                <div class="p-3 bg-light text-start">
                    <p class="mb-0">Need assistance? Contact support at
                        <a href="mailto:hello@topship.africa" target="_blank">hello@topship.africa</a> or call
                        <a href="tel:02013302594" target="_blank">02013302594</a>.
                    </p>
                </div>
            </div>
            `,

            });
            app.mount('#app');
        });
    </script>
    <?php
}



public static function topship_contact_us_page(){

        if (!current_user_can('manage_options')) {
            return;
        }
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
                    <div>

                        <div style="">
                            <div class="row" style="">
                                <div class="col-md-5">
                                    <div style="text-align: start; width: 80%">
                                        <h1 class="fw-bold">Contact Us</h1>
                                        <p>Our team is here to help. Please fill out the form and we will be in touch in 24 hours or sooner.</p>

                                    </div>
                                    <div class="imgdev">
                                        <img class="rotate-90" src="<?php echo  plugin_dir_url(__FILE__) . '../image/path.png'?>" alt="image"/>
                                    </div>
                                </div>
                                <div id="app" class="col-md-7">
                                    <contact-component></contact-component>
                                </div>
                            </div>
                        </div>


                    </div>
                </div>
            </div>
        </div>
        <dial :show="show"/>
        <mess :title="message.title" :message="message.message" :show="message.show"  @closed="closed"/>

    </div>

    <script>

        document.addEventListener('DOMContentLoaded', function () {
            // Define the Vue app
            var app = Vue.createApp({
                data() {
                    return {
                        show: false,
                        title: '',
                        message: '',
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

            app= utilityVueComponent(app);
            // Define the RegistrationComponent
            app.component('contact-component', {
                template: `
    <div class="card shadow-sm p-4">
                        <form class="contact-form" @submit.prevent="submitForm">
                            <div class="mb-3">
                                <label for="fullName" class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" id="fullName" v-model="input.fullName" class="form-control" required />
                            </div>

                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                <input type="tel" id="phone" v-model="input.phone" class="form-control" required />
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" id="email" v-model="input.email" class="form-control" required />
                            </div>

                            <div class="mb-3">
                                <label for="businessName" class="form-label">Business Name <span class="text-danger">*</span></label>
                                <input type="text" id="businessName" v-model="input.name" class="form-control" required />
                            </div>

                            <div class="mb-3">
                                <label for="website" class="form-label">Business Website</label>
                                <input type="url" id="website" v-model="input.website" class="form-control" />
                            </div>

                            <div class="mb-3">
                                <label for="message" class="form-label">Your Message <span class="text-danger">*</span></label>
                                <textarea id="message" v-model="input.message" class="form-control" rows="5" required></textarea>
                            </div>

                            <hr class="my-4" />
                            <button type="submit" class="btn btn-primary w-100">Submit</button>
                        </form>
                    </div>
            <dial :show="show"/>
            <mess :title="message.title" :message="message.message" :show="message.show"  @closed="closed"/>

            `,
                props: ['base_url', 'contact_post_url'],
                data() {
                    return {
                        input: {
                            fullName: '',
                            email: '',
                            phone: '',
                            name: '',
                            website: '',
                            message: ''
                        },
                        message: {
                            title: 'My title',
                            message: 'my message to you',
                            show: false
                        },
                        show: false
                    };
                },
                methods: {
                    showMessage(title, message) {
                        this.message.title = title;
                        this.message.message = message;
                        this.message.show = true;
                    },
                    closed() {
                        this.message.show = false;
                    },
                    submitForm() {
                        this.show = true;
                        axios.post('<?php echo esc_url(home_url('/wp-json/topship/v1/contact')); ?>', this.input)
                            .then(response => {
                                this.show = false;
                                this.showMessage('Success', 'Your message has been sent successfully.');
                                this.resetForm();
                            })
                            .catch(error => {
                                this.show = false;
                                this.showMessage('Error', 'There was an error sending your message.');
                            });
                    },
                    resetForm() {
                        this.input = {
                            fullName: '',
                            email: '',
                            phone: '',
                            name: '',
                            website: '',
                            message: ''
                        };
                    }
                }

            });
            app.mount('#app');
        });
    </script>
        <?php
    }







}
