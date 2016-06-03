@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-3">
            <div class="panel panel-default">
                <div class="panel-heading">Menu block</div>

                <div class="panel-body">
                    <ul class="nav nav-list">
                        <li class="nav-header">Личная информация</li>
                        <li><a href="#">Редактировать</a></li>
                        <li><a href="#">Пароль</a></li>
                        <li class="nav-header">Знания</li>
                        <li><a href="#">Мои</a></li>
                        <li><a href="#">Подписан</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <div>
                   @yield('profile-content')
            </div>
        </div>
    </div>
</div>
@endsection
