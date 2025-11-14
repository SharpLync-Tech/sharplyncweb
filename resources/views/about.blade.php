@extends('about-base')

@section('content')
<div class="container py-5">
    <!-- My Story Section -->
    <div class="my-story-section mb-5">
        <div class="card wow fadeInUp" data-wow-duration="1.2s">
            <div class="card-body">
                <h2 class="card-title">My Story</h2>
                <p class="card-text">
                    <!-- My Story content -->
                    Welcome to SharpLync! I'm [Name], the founder of SharpLync. This platform was born from a passion to connect people with cutting-edge solutions...
                </p>
            </div>
        </div>
    </div>
    <!-- Testimonials Section -->
    <div class="testimonials-section">
        <h2 class="mb-4">Testimonials</h2>
        <div class="row">
            <!-- Testimonial 1 -->
            <div class="col-md-6">
                <div class="testimonial card mb-4 p-3">
                    <blockquote class="blockquote mb-0">
                        <p>SharpLync completely transformed the way I manage my business communications. It's intuitive and powerful... </p>
                    </blockquote>
                    <button type="button" class="btn btn-link p-0" data-toggle="modal" data-target="#testimonial1Modal">
                        Read more...
                    </button>
                </div>
            </div>
            <!-- Testimonial 2 -->
            <div class="col-md-6">
                <div class="testimonial card mb-4 p-3">
                    <blockquote class="blockquote mb-0">
                        <p>Thanks to SharpLync, our team collaboration has improved dramatically. The platform is user-friendly and very efficient... </p>
                    </blockquote>
                    <button type="button" class="btn btn-link p-0" data-toggle="modal" data-target="#testimonial2Modal">
                        Read more...
                    </button>
                </div>
            </div>
            <!-- Additional testimonials can be added similarly -->
        </div>
    </div>
</div>
@endsection

@section('modals')
<!-- Testimonial 1 Modal -->
<div class="modal fade" id="testimonial1Modal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Testimonial</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span>&times;</span>
        </button>
      </div>
      <div class="modal-body">
        SharpLync completely transformed the way I manage my business communications. It's intuitive and powerful, providing all the tools I need in one place. I highly recommend SharpLync to any business looking to streamline their communication processes.
      </div>
    </div>
  </div>
</div>
<!-- Testimonial 2 Modal -->
<div class="modal fade" id="testimonial2Modal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Testimonial</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span>&times;</span>
        </button>
      </div>
      <div class="modal-body">
        Thanks to SharpLync, our team collaboration has improved dramatically. The platform is user-friendly and very efficient. We have seen a significant boost in productivity since adopting SharpLync.
      </div>
    </div>
  </div>
</div>
@endsection
