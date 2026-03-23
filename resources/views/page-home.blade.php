{{--
  Template Name: Flux Press Home Demo
--}}

@php
    $demo = get_theme_mod('flux_home_demo', 'corporate');
@endphp

@extends('layouts.home', ['demo' => $demo])
