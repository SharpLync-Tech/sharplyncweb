@extends('layouts.testbase')
@section('title', 'SharpLync | Home')

@section('content')
<section class="hero">
  <div class="hero-text">    
    <h1>Coming Soon<br></h1>
    
    
    <div class="hero-buttons">
      <button class="btn-accent" onclick="window.location.href='/'">Back to Home</button>
    </div>   

  <div class="hero-image">
    <img src="{{ asset('images/hero-cpu.png') }}" alt="SharpLync CPU Circuit Graphic">
  </div>
</section>
@endsection