<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PDF</title>
  <style>
    body {
      font-family: Arial, sans-serif;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin: 20px 0;
      font-size: 12px;
      text-align: left;
      /* table-layout: fixed; */
    }

    table th,
    table td {
      border: 1px solid #dddddd;
      padding: 2px;
    }

    table th {
      background-color: #f2f2f2;
      color: #333;
    }

    table tr {
      text-align: center;
    }

    table tr:nth-child(even) {
      background-color: #f9f9f9;
    }

    table tr:hover {
      background-color: #f1f1f1;
    }

    h1 {
      font-size: 18px;
      text-align: center;
      color: #333;
    }
  </style>
</head>

<body>
  <h1>Reportes de Citas del mes Actual</h1>
  <table border="1">
    <thead>
      <tr>
        <th>Id del doctor</th>
        <th>Id de cita</th>
        <th>Id del cuarto</th>
        <th>Id de especial</th>
        <th>Hora inicio de cita</th>
        <th>Hora final de cita</th>
      </tr>
    </thead>
    <tbody>
      @if($data->isEmpty())
      <tr>
      <td colspan="6">No hay datos disponibles.</td>
      </tr>
    @else
      @foreach($data as $item)
      <tr>
      <td>{{ $item->doctor_id }}</td>
      <td>{{ $item->appointment_id }}</td>
      <td>{{ $item->room_id }}</td>
      <td>{{ $item->specialization_id }}</td>
      <td>{{ $item->appointment_start_timestamp }}</td>
      <td>{{ $item->appointment_end_timestamp }}</td>
      </tr>
    @endforeach
    @endif
    </tbody>
  </table>
</body>

</html>