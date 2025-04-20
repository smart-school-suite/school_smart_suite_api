<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <title>Document</title>
</head>
<body>
    <div class="container">
        <div class="d-flex flex-column">
            <span>{{$exam->examtype->name}}</span>
        </div>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Position</th>
                    <th>Student Names</th>
                    <th>Total Scores</th>
                    <th>GPA</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($examResults as $examResult)
                    <tr>
                        <td>{{ $standing->rank }}</td>
                        <td>{{ $examResult->student->name }}</td>
                        <td>{{ $standing->total_score }}</td>
                        <td>{{ $standing->gpa }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>
