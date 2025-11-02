@extends('layouts.testbase')
@section('title', 'SharpLync | Home')

@section('content')
<section class="hero">
  <div class="hero-text">    
    <h1>Coming Soon<br></h1> 
    
    <!-- Integrated SharpLync Pulse Card -->
    <div class="hero-threat-card" id="heroThreatCard">
      <div class="hero-threat-content" id="heroThreatContent">
        
      </div>
    </div>
  </div>

  <div class="hero-image">
    <img src="{{ asset('images/hero-cpu.png') }}" alt="SharpLync CPU Circuit Graphic">
  </div>
</section>
@endsection