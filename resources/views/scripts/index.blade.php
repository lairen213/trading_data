@extends('templates.main')

@section('title', 'Trading script')

@section('content')
    <div class="container">
        <div class="row mt-5">
            @if(!$data_week && !$data_day):
                <div class="col-12">
                    <div class="alert alert-warning">
                        Слишком много запросов. Попробуйте позже...
                    </div>
                </div>
            @endif
            <div class="col-12">
                <div class="alert alert-success col-12" role="alert">
                    <p>Укажите диапазон дат:</p>
                    <div class="row">
                        <div class="col-4">
                            <div class="form-floating mb-3">
                                <input type="date" value="{{$date_from}}" class="form-control" name="date_start" id="date_from_inp">
                                <label for="date_from_inp">От</label>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="form-floating mb-3">
                                <input class="form-control" type="date" value="{{$date_to}}" name="date_end" id="date_to_inp">
                                <label for="date_to_inp">До</label>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="form-floating">
                                <select class="form-select" id="pairSelect" disabled>
                                    <option selected>EUR/USD</option>
                                </select>
                                <label for="pairSelect">Торговая пара:</label>
                            </div>
                            <br>
                            <button class="btn btn-info float-end" id="btnStart">Анализ</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="row">
            <div class="col-4">
                <div class="alert alert-info" role="alert">
                    Средний процент движения за <strong>день</strong>: <br>
                    <span style="font-size: 18px;"><strong>{{$avg_percent_day}} %</strong></span>
                </div>
            </div>

            <div class="col-4 offset-4">
                <div class="alert alert-info" role="alert">
                    Средний процент движения за <strong>неделю</strong>: <br>
                    <span style="font-size: 18px;"><strong>{{$avg_percent_week}} %</strong></span>
                </div>
            </div>
        </div>

        <hr>

        <div class="row">
            <div class="col-12">
                <h1>Week info</h1>
                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th scope="col">Дата (Пятница)</th>
                        <th scope="col">Хай</th>
                        <th scope="col">Лой</th>
                        <th scope="col">Процент движения за неделю</th>
                        <th scope="col">В сравнении с предыдущей неделей</th>
                    </tr>
                    </thead>
                    <tbody>

                    @foreach($data_week as $dat)
                        <tr>
                            <th scope="row">{{$dat['date']}}</th>
                            <td>{{$dat['high']}}</td>
                            <td>{{$dat['low']}}</td>
                            <td>{{$dat['percent']}}</td>
                            <td> <span class="badge {{ ($dat['previous_date'] >= 0) ? "bg-success" : "bg-danger" }}">{{$dat['previous_date']}} %</span>  </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <hr>
            <div class="col-12">
                <h1>Day info</h1>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th scope="col">Дата</th>
                            <th scope="col">Хай</th>
                            <th scope="col">Лой</th>
                            <th scope="col">Процент движения за день</th>
                            <th scope="col">День недели</th>
                            <th scope="col">В сравнении с предыдущим днем</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($data_day as $dat)
                        <tr>
                            <th scope="row">{{$dat['date']}}</th>
                            <td>{{$dat['high']}}</td>
                            <td>{{$dat['low']}}</td>
                            <td>{{$dat['percent']}}</td>
                            <td>{{$dat['day_of_week']}}</td>
                            <td> <span class="badge {{ ($dat['previous_date'] >= 0) ? "bg-success" : "bg-danger" }}">{{$dat['previous_date']}} %</span>  </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function removeURLParameter(url, parameter) {
            //prefer to use l.search if you have a location/link object
            var urlparts = url.split('?');
            if (urlparts.length >= 2) {

                var prefix = encodeURIComponent(parameter) + '=';
                var pars = urlparts[1].split(/[&;]/g);

                //reverse iteration as may be destructive
                for (var i = pars.length; i-- > 0;) {
                    //idiom for string.startsWith
                    if (pars[i].lastIndexOf(prefix, 0) !== -1) {
                        pars.splice(i, 1);
                    }
                }

                return urlparts[0] + (pars.length > 0 ? '?' + pars.join('&') : '');
            }
            return url;
        }

        $('#btnStart').on('click', function(){
            $(this).prop('disabled', true);
            let date_from_inp = $('#date_from_inp').val();
            let date_to_inp = $('#date_to_inp').val();

            if(date_from_inp > date_to_inp){
                swal('Дата ОТ не может быть позже даты ДО');
                $(this).prop('disabled', false);
            }else {

                let url = removeURLParameter(window.location.href, 'date_to');
                url = removeURLParameter(window.location.href, 'date_from');
                if (url.indexOf('?') > -1) {
                    url += '&date_from=' + date_from_inp + '&date_to=' + date_to_inp;
                } else {
                    url += '?date_from=' + date_from_inp + '&date_to=' + date_to_inp;
                }
                window.location.href = url;
            }
        })
    </script>
@endsection
