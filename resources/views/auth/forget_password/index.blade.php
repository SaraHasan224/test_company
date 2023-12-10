@extends('layouts.master')

@section('auth_layout', true)
@section('content')
    <div class="app-container app-theme-white body-tabs-shadow">
        <div class="app-container">
            <div class="h-100">
                <div class="h-100 no-gutters row">
                    <div class="d-none d-lg-block col-lg-4">
                        <div class="slider-light">
                            <div class="slick-slider">
                                <div>
                                    <div class="position-relative h-100 d-flex justify-content-center align-items-center bg-org-plate"
                                         tabindex="-1">
                                        <div class="slide-img-bg"
                                             style="background-image: url('assets/images/originals/city.jpg');"></div>
                                        <div class="slider-content"><h3>Perfect Balance</h3>
                                            <p>ArchitectUI is like a dream. Some think it's too good to be true!
                                                Extensive collection of unified React Boostrap Components and
                                                Elements.</p></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="position-relative h-100 d-flex justify-content-center align-items-center bg-premium-dark"
                                         tabindex="-1">
                                        <div class="slide-img-bg"
                                             style="background-image: url('assets/images/originals/citynights.jpg');"></div>
                                        <div class="slider-content"><h3>Scalable, Modular, Consistent</h3>
                                            <p>Easily exclude the components you don't require. Lightweight, consistent
                                                Bootstrap based styles across all elements and components</p></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="position-relative h-100 d-flex justify-content-center align-items-center bg-sunny-morning"
                                         tabindex="-1">
                                        <div class="slide-img-bg"
                                             style="background-image: url('assets/images/originals/citydark.jpg');"></div>
                                        <div class="slider-content"><h3>Complex, but lightweight</h3>
                                            <p>We've included a lot of components that cover almost all use cases for
                                                any type of application.</p></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="h-100 d-flex bg-white justify-content-center align-items-center col-md-12 col-lg-8 d-none">
                        @csrf
                        <div class="mx-auto app-login-box col-sm-12 col-md-10 col-lg-9">
                            <div class="app-logo"></div>
                            <h4>
                                <div>Forgot your Password?</div>
                            </h4>
                            <span>
                                    {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
                            </span>
                            <div class="mt-5">
                                <!-- Session Status -->
                                {{--<x-auth-session-status class="mb-4" :status="session('status')" />--}}
                                <form method="POST" action="{{ route('password.email') }}">
                                    @csrf
                                    <div class="form-row">
                                        <div class="col-md-12">
                                            @if(session('status'))
                                                <div class="alert alert-success col-12">
                                                    <strong>Success - </strong> {{ session('status') }}
                                                </div>
                                            @endif
                                            @if(session('error'))
                                                <div class="alert alert-danger col-12">
                                                    <strong>Error - </strong> {{ session('error') }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="col-md-12">
                                            <div class="position-relative form-group">
                                                <label for="exampleEmail"  class="">{{ __('Enter Email Address') }}</label>
                                                <input
                                                    name="email"
                                                    id="email"
                                                    placeholder="Enter Email Address"
                                                    value="{{$email ?? old('email')}}"
                                                    type="email"
                                                    class="form-control"
                                                    required
                                                    maxlength="100"
                                                >
                                            </div>
                                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                                        </div>
                                    </div>
                                    <div class="mt-4 d-flex align-items-center">
                                        <h6 class="mb-0">
                                            <a href="{{ route('login')}}" class="text-primary">{{ __('Sign in existing account') }}</a>
                                        </h6>
                                        <div class="ml-auto">
                                            <button class="btn btn-primary btn-lg">{{ __('Send Password Reset Link') }}</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection