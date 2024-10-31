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

    public static function topship_register(){ ?>
 
 <div class="container">

<div class="row">
<div class="col-md-10 mx-auto p-4">
    <?php self::render_navigation()  ?>

<div class="shadow-lg bg-white p-5">


<h2 class="mt-5fw-bold mb-5">Create your Topship account</h2>
         
<form>

<div class="form-row">
<div class="form-group col-md-6 col-lg-6 col-sm-12">
  <label for="firstName" class="mb-0">First Name *</label>
  <input type="text" class="form-control" id="firstName" placeholder="Stark" required>
</div>
<div class="form-group col-md-6 col-lg-6 col-sm-12">
  <label for="lastName" class="mb-0">Last Name *</label>
  <input type="text" class="form-control" id="lastName" placeholder="Stark" required>
</div>

</div>

<div class="form-group">
  <label for="phone" class="mb-0">Phone Number *</label>
  <input type="tel" class="form-control" id="phone" placeholder="Enter phone number" required>
</div>
<div class="form-group">
  <label for="email" class="mb-0">Email Address *</label>
  <input type="email" class="form-control" id="email" placeholder="Enter email address" required>
</div>
<div class="form-group">
  <label for="address" class="mb-0">Address *</label>
  <input type="text" class="form-control" id="address" placeholder="Enter address" required>
</div>
<div class="form-row">
  <div class="form-group col-md-6">
    <label for="country" class="mb-0">Country *</label>
    <select class="form-control" id="country" required>
      <option>Select Country</option>
    </select>
  </div>
  <div class="form-group col-md-6">
    <label for="state" class="mb-0">State *</label>
    <select class="form-control" id="state" required>
      <option>Select State</option>
    </select>
  </div>
</div>
<div class="form-group">
  <label for="city" class="mb-0">City *</label>
  <select class="form-control" id="city" required>
    <option>Select City</option>
  </select>
</div>
<div class="form-group">
  <label for="postalCode" class="mb-0">Postal Code (optional)</label>
  <input type="text" class="form-control" id="postalCode" placeholder="Enter postal code">
</div>
<div class="form-group">
  <label for="password" class="mb-0">Password *</label>
  <input type="password" class="form-control" id="password" placeholder="Enter password" required>
</div>
<button type="submit" class="btn btn-primary w-100">Register</button>
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
  <div class="form-group">
    <label for="fullName">Full Name *</label>
    <input type="text" class="form-control" id="fullName" placeholder="Enter full name" required>
  </div>
  <div class="form-group">
    <label for="phone">Phone Number *</label>
    <input type="tel" class="form-control" id="phone" placeholder="Enter phone number"  required>
  </div>
  <div class="form-group">
    <label for="email">Email Address *</label>
    <input type="email" class="form-control" id="email" placeholder="Enter email address"  required>
  </div>
  <div class="form-group">
    <label for="businessName">Business Name *</label>
    <input type="text" class="form-control" id="businessName" placeholder="Enter business name" required>
  </div>
  <div class="form-group">
    <label for="businessWebsite">Business Website (optional)</label>
    <input type="url" class="form-control" id="businessWebsite" placeholder="Enter business website">
  </div>
  <div class="form-group">
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



    public static function topship_contact_guide_page(){

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
  <div class="form-group">
    <label for="fullName">Full Name *</label>
    <input type="text" class="form-control" id="fullName" placeholder="Enter full name" required>
  </div>
  <div class="form-group">
    <label for="phone">Phone Number *</label>
    <input type="tel" class="form-control" id="phone" placeholder="Enter phone number"  required>
  </div>
  <div class="form-group">
    <label for="email">Email Address *</label>
    <input type="email" class="form-control" id="email" placeholder="Enter email address"  required>
  </div>
  <div class="form-group">
    <label for="businessName">Business Name *</label>
    <input type="text" class="form-control" id="businessName" placeholder="Enter business name" required>
  </div>
  <div class="form-group">
    <label for="businessWebsite">Business Website (optional)</label>
    <input type="url" class="form-control" id="businessWebsite" placeholder="Enter business website">
  </div>
  <div class="form-group">
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
