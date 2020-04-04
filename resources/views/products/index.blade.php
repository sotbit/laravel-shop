@extends('layouts.app')
@section('title', 'Product list')

@section('content')
    <div class="row">
        <div class="col-lg-10 col-lg-offset-1">
            <div class="panel panel-default">
                <div class="panel-body">
                    
                    <div class="row">
                        <form action="{{ route('products.index') }}" class="form-inline search-form">

                            
                            <input type="hidden" name="filters">

                            
                            
                            <a class="all-products" href="{{ route('products.index') }}">All</a> &gt;
                            
                            @if ($category)
                                
                                @foreach($category->ancestors as $ancestor)
                                
                                <span class="category">
                                  <a href="{{ route('products.index', ['category_id' => $ancestor->id]) }}">{{ $ancestor->name }}</a>
                                </span>
                                <span>></span>
                                @endforeach
                                
                                <span class="category">{{ $category->name }}</span><span> ></span>
                                
                                <input type="hidden" name="category_id" value="{{ $category->id }}">
                            @endif
                            

                            
                            
                            @foreach($propertyFilters as $name => $value)
                            <span class="filter">{{ $name }}:
                            <span class="filter-value">{{ $value }}</span>
                            
                            <a class="remove-filter" href="javascript: removeFilterFromQuery('{{ $name }}')">×</a>
                            </span>
                            @endforeach
                            

                            <input type="text" class="form-control input-sm" name="search" placeholder="搜索">
                            <button class="btn btn-primary btn-sm">search for</button>
                            <select name="order" class="form-control input-sm pull-right">
                                <option value="">Sort by</option>
                                <option value="price_asc">Price from low to high</option>
                                <option value="price_desc">Price from high to low</option>
                                <option value="sold_count_desc">Sales from high to low</option>
                                <option value="sold_count_asc">Sales from low to high</option>
                                <option value="rating_desc">Evaluation from high to low</option>
                                <option value="rating_asc">Evaluation from low to high</option>
                            </select>
                        </form>
                    </div>
                    

                    
                    <div class="filters">
                        
                        @if ($category && $category->is_directory)
                            <div class="row">
                                <div class="col-xs-3 filter-key">Child eyes:</div>
                                <div class="col-xs-9 filter-values">
                                    
                                    @foreach($category->children as $child)
                                        <a href="{{ route('products.index', ['category_id' => $child->id]) }}">{{ $child->name }}</a>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        
                        
                        @foreach($properties as $property)
                            <div class="row">
                                
                                <div class="col-xs-3 filter-key">{{ $property['key'] }}：</div>
                                <div class="col-xs-9 filter-values">
                                    
                                    @foreach($property['values'] as $value)
                                        <a href="javascript: appendFilterToQuery('{{ $property['key'] }}', '{{ $value }}');">{{ $value }}</a>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                        

                    </div>
                    

                    <div class="row products-list">
                        @foreach($products as $product)
                            <div class="col-xs-3 product-item">
                                <div class="product-content">
                                    <div class="top">
                                        <div class="img">
                                            <a href="{{ route('products.show', ['product' => $product->id]) }}">
                                                <img src="{{ $product->image_url }}" alt="">
                                            </a>
                                        </div>
                                        <div class="price"><b>$</b>{{ $product->price }}</div>
                                        <div class="title">
                                            <a href="{{ route('products.show', ['product' => $product->id]) }}">{{ $product->title }}</a>
                                        </div>
                                    </div>
                                    <div class="bottom">
                                        <div class="sold_count">Sales <span>{{ $product->sold_count }}pen</span></div>
                                        <div class="review_count">Evaluation <span>{{ $product->review_count }}</span></div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="pull-right">{{ $products->appends($filters)->render() }}</div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scriptsAfterJs')
    <script>
        var filters = {!! json_encode($filters) !!};
        $(document).ready(function () {
            $('.search-form input[name=search]').val(filters.search);
            $('.search-form select[name=order]').val(filters.order);
            $('.search-form select[name=order]').on('change', function() {
                $('.search-form').submit();
            });

            $('.search-form select[name=order]').on('change', function() {
                var searches = parseSearch();
                if (searches['filters']) {
                    $('.search-form input[name=filters]').val(searches['filters']);
                }
                $('.search-form').submit();
            });

        })

        function parseSearch() {
            var searches = {};
            location.search.substr(1).split('&').forEach(function (str) {
                var result = str.split('=');
                searches[decodeURIComponent(result[0])] = decodeURIComponent(result[1]);
            });

            return searches;
        }

        function buildSearch(searches) {
            var query = '?';
            _.forEach(searches, function (value, key) {
                query += encodeURIComponent(key) + '=' + encodeURIComponent(value) + '&';
            });
            return query.substr(0, query.length - 1);
        }

        function appendFilterToQuery(name, value) {
            var searches = parseSearch();
            if (searches['filters']) {
                searches['filters'] += '|' + name + ':' + value;
            } else {
                searches['filters'] = name + ':' + value;
            }
            location.search = buildSearch(searches);
        }

        function removeFilterFromQuery(name) {
            var searches = parseSearch();
            if(!searches['filters']) {
                return;
            }

            var filters = [];
            searches['filters'].split('|').forEach(function (filter) {
                var result = filter.split(':');
                if (result[0] === name) {
                    return;
                }
                filters.push(filter);
            });
            searches['filters'] = filters.join('|');
            location.search = buildSearch(searches);
        }

    </script>
@endsection