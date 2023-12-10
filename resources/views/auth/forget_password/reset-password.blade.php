@extends('layouts.master')

@section('auth_layout', true)
@section('content')
    <div class="app-container app-theme-white body-tabs-shadow">
        <div class="app-container">
            <div class="h-100">
                <div class="h-100 no-gutters row">
                    <div class="d-none d-lg-block col-lg-4">
                        @include('auth.common.slider')
                    </div>
                    <div class="h-100 d-flex bg-white justify-content-center align-items-center col-md-12 col-lg-8">
                        @csrf
                        <div class="mx-auto app-login-box col-sm-12 col-md-10 col-lg-9">
                            <div class="app-logo"></div>
                            <h4>
                                <div>Reset your Password?</div>
                            </h4>
                            <div class="mt-5">
                                <!-- Session Status -->
                                {{--<x-auth-session-status class="mb-4" :status="session('status')" />--}}
                                <form method="POST" action="{{ route('password.store') }}">
                                    @csrf
                                    <!-- Password Reset Token -->
                                    <input type="hidden" name="token" value="{{ $request->route('token') }}">
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
                                                <b>
                                                    <label for="exampleEmail"  class="">{{ __('Enter Email Address') }}</label>
                                                </b>
                                                <input
                                                        name="email"
                                                        id="email"
                                                        placeholder="Enter Email Address"
                                                        value="{{old('email', $request->email)}}"
                                                        type="email"
                                                        class="form-control block mt-1 w-full"
                                                        required
                                                        autofocus
                                                        autocomplete="username"
                                                >
                                            </div>
                                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="col-md-12">
                                            <div class="position-relative form-group">
                                                <label for="exampleEmail"  class="">{{ __('Enter New Password') }}</label>
                                                <input
                                                        name="password"
                                                        id="password"
                                                        placeholder="*********"
                                                        type="password"
                                                        class="form-control block mt-1 w-full"
                                                        required
                                                        autocomplete="new-password"
                                                >
                                            </div>
                                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="col-md-12">
                                            <div class="position-relative form-group">
                                                <label for="exampleEmail"  class="">{{ __('Confirm Password') }}</label>
                                                <input
                                                        name="password_confirmation"
                                                        id="password_confirmation"
                                                        placeholder="*********"
                                                        value="{{$email ?? old('email')}}"
                                                        type="password"
                                                        class="form-control block mt-1 w-full"
                                                        required
                                                        autocomplete="new-password"
                                                >
                                            </div>
                                            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                                        </div>
                                    </div>
                                    <div class="mt-4 d-flex align-items-center">
                                        <div class="ml-auto">
                                            <button class="btn btn-primary btn-lg">{{ __('Reset Password') }}</button>
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