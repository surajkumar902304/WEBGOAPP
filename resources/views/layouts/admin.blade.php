<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'GOAPP') }}</title>

    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/@mdi/font/css/materialdesignicons.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
    [v-cloak] {
      display: none !important;
    }
  </style>
</head>
<body>
    <div id="app" v-cloak>
        <v-app app>
            <v-navigation-drawer permanent expand-on-hover fixed class="grey lighten-3" elevation="16">
                <v-list class="border border-bottom">
                    <v-list-item class="px-2">
                      <v-list-item-avatar>
                        <v-img src="{{asset('images/icon.png')}}"></v-img>
                      </v-list-item-avatar>
                        <v-list-item-content>
                            <v-list-item-title class="text-h6">
                                TrueWeb App
                            </v-list-item-title>
                        </v-list-item-content>
                    </v-list-item>
                </v-list>
                <v-list dense nav shaped>
                    <v-list-item-group>
                        <v-list-item href="/admin/dashboard" class="{{ request()->routeIs('admin.index') ? 'active' : '' }}">
                            <v-list-item-icon>
                              <v-icon>mdi-view-dashboard-outline</v-icon>
                            </v-list-item-icon>
                            <v-list-item-title>Dashboard</v-list-item-title>
                        </v-list-item>
                    </v-list-item-group>
                </v-list>
            </v-navigation-drawer>
            <v-main style="padding-left: 66px" class="py-5 pe-3">
                @yield('content')
            </v-main>
        </v-app>
    </div>
    <script src="{{asset('js/vapp.js')}}"></script>
</body>
</html>
