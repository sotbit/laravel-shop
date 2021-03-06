<nav class="navbar navbar-default navbar-static-top">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#app-navbar-collapse">
                <span class="sr-only">Toggle Navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="{{ url('/') }}">
                Laravel Shop
            </a>
        </div>

        <div class="collapse navbar-collapse" id="app-navbar-collapse">
            <ul class="nav navbar-nav">
                
                
                @if(isset($categoryTree))
                    <li>
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">All categories <b class="caret"></b></a>
                        <ul class="dropdown-menu multi-level">
                            
                            @each('layouts._category_item', $categoryTree, 'category')
                        </ul>
                    </li>
                @endif
                
            </ul>
            <ul class="nav navbar-nav navbar-right">
                
                @guest
                    <li><a href="{{ route('login') }}">log in</a></li>
                    <li><a href="{{ route('register') }}">registered</a></li>
                @else
                    <li>
                        <a href="{{ route('cart.index') }}"><span class="glyphicon glyphicon-shopping-cart" aria-hidden="true"></span></a>
                    </li>
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                            <span class="user-avatar pull-left" style="margin-right:8px; margin-top:-5px;">
                                <img src="https://iocaffcdn.phphub.org/uploads/images/201709/20/1/PtDKbASVcz.png?imageView2/1/w/60/h/60" class="img-responsive img-circle" width="30px" height="30px">
                            </span>
                            {{ Auth::user()->name }} <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu" role="menu">
                            <li>
                                <a href="{{ route('user_addresses.index') }}">Shipping address</a>
                            </li>
                            <li>
                                <a href="{{ route('orders.index') }}">My Order</a>
                            </li>
                            <li>
                                <a href="{{ route('installments.index') }}">Installment</a>
                            </li>
                            <li>
                                <a href="{{ route('products.favorites') }}">My collection</a>
                            </li>
                            <li>
                                <a href="{{ route('logout') }}"
                                   onclick="event.preventDefault();
                                             document.getElementById('logout-form').submit();">
                                    Sign out
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                    {{ csrf_field() }}
                                </form>
                            </li>
                        </ul>
                    </li>
            @endguest
            
            </ul>
        </div>

    </div>
</nav>