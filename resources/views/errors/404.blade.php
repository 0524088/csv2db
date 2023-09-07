@extends('layouts.base')
@section('title', '首頁')
@section('content')
    <div class="container py-5 h-100">
        <div class="row d-flex justify-content-center align-items-center h-100">
            <div class="col-12 col-md-8 col-lg-6 col-xl-5">
                <div class="card bg-dark text-white" style="border-radius: 1rem;">
                    <div class="card-body p-5 text-center">
                        <div class="mb-md-5 mt-md-4 pb-5">
                            <x-alert type="danger" message="page not found" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection