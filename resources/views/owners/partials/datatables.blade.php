@if (isset($datatables) && count($datatables) > 0)
    @foreach($datatables as $id => $table)
        @if (!isset($table['type']) || (isset($table['type']) && $table['type'] == 'poll' && $table['data']))
            <section class="dataTableWrapper table-responsive ajax-lock" data-ajax-queue="async-{{ $id }}">
                <header>
                    <div class="headings">
                        @if (isset($table['title']))<h1>{{ $table['title'] }}</h1>@endif
                        @if (isset($table['subtitle']))<h2>{{ $table['subtitle'] }}</h2>@endif
                        @if (isset($table['smalltitle']))<h4>{!! $table['smalltitle'] !!}</h4>@endif
                    </div>
                    @if (isset($table['buttons']))
                    <div class="btn-group-wrapper">
                        @foreach($table['buttons'] as $button)
                        <div class="btn-group">
                            <a data-table="{{ $id }}" href="{{ $button['url'] }}" class="btn {{ $button['class'] }}">
                                @if ($button['icon'])<span class="glyphicon glyphicon-{{ $button['icon'] }}"></span>@endif
                                {{ $button['name'] }}
                            </a>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </header>
                <table id="datatable{{ $id }}" class="dataTable table {{ $table['class'] }}">
                @if (isset($table['footer']))
                    <tfoot>
                        <tr @if (isset($table['footer']['class']))class="{{ $table['footer']['class'] }}"@endif>
                        @foreach($table['footer']['columns'] as $column)
                            <th @if (isset($column['class']))class="{{ $column['class'] }}"@endif>{!! $column['data'] !!}</th>
                        @endforeach
                        </tr>
                    </tfoot>
                @endif
                </table>
            </section>
            @if (count($datatables) > 1)<hr>@endif
        @endif
    @endforeach
@endif
