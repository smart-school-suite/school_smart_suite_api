<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>{{ $title }}</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            margin: 20px;
            color: #333;
        }

        h1 {
            text-align: center;
            font-size: 20px;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th {
            background: #f2f2f2;
            padding: 8px;
            border: 1px solid #ccc;
            font-weight: bold;
            text-transform: capitalize;
        }

        td {
            padding: 8px;
            border: 1px solid #ccc;
        }

        .text-center {
            text-align: center;
        }

        .small {
            font-size: 10px;
        }
    </style>
</head>

<body>

    {{-- PDF Title --}}
    <h1>{{ $title }}</h1>

    {{-- Data Table --}}
    <table>
        <thead>
            <tr>
                @foreach($columns as $column)
                    <th>{{ str_replace('_', ' ', $column) }}</th>
                @endforeach
            </tr>
        </thead>

        <tbody>
            @foreach($teachers as $teacher)
                <tr>
                    @foreach($columns as $column)
                        <td>
                            {{ $teacher[$column] ?? '—' }}
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
