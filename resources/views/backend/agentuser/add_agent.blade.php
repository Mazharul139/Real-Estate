@extends('admin.admin_dashboard')
@section('admin')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>


<div class="page-content">



        <div class="row profile-body">
          <!-- left wrapper start -->
          
          <!-- left wrapper end -->
          <!-- middle wrapper start -->
          <div class="col-md-8 col-xl-8 middle-wrapper">
            <div class="row">

            <div class="card">
              <div class="card-body">

								<h6 class="card-title">Add Agent</h6>

								<form id="myForm" method="POST" action="{{route('store.agent')}}" class="forms-sample">
                  @csrf

                  <div class="form-group mb-3">
										<label for="name" class="form-label">Agent Name</label>
										<input type="text" name="name" class="form-control">
										
				</div>
                <div class="form-group mb-3">
										<label for="email" class="form-label">Email</label>
										<input type="text" name="email" class="form-control">
										
				</div>

                <div class="form-group mb-3">
										<label for="phone" class="form-label">Phone</label>
										<input type="text" name="phone" class="form-control">
										
				</div>

                <div class="form-group mb-3">
										<label for="address" class="form-label">Address</label>
										<input type="text" name="address" class="form-control">
										
				</div>
                <div class="form-group mb-3">
										<label for="password" class="form-label">Password</label>
										<input type="password" name="password" class="form-control">
										
				</div>



               

				
                <button type="submit" class="btn btn-primary me-2">Save changes</button>
									
</form>

              </div>
            </div>

              
            </div>
          </div>
          <!-- middle wrapper end -->
          <!-- right wrapper start -->
          <!-- right wrapper end -->
        </div>

			</div>

            <script type="text/javascript">
    $(document).ready(function (){
        $('#myForm').validate({
            rules: {
                name: {
                    required : true,
                }, 

                email: {
                    required : true,
                }, 

                phone: {
                    required : true,
                }, 

                password: {
                    required : true,
                }, 
                
            },
            messages :{
                name: {
                    required : 'Please Enter Agent Name',
                },
                
                email: {
                    required : 'Please Enter Agent Email',
                },

                phone: {
                    required : 'Please Enter Agent Phone',
                },

                password: {
                    required : 'Please Enter Agent Password',
                },
                 

            },
            errorElement : 'span', 
            errorPlacement: function (error,element) {
                error.addClass('invalid-feedback');
                element.closest('.form-group').append(error);
            },
            highlight : function(element, errorClass, validClass){
                $(element).addClass('is-invalid');
            },
            unhighlight : function(element, errorClass, validClass){
                $(element).removeClass('is-invalid');
            },
        });
    });
    
</script>



			@endsection