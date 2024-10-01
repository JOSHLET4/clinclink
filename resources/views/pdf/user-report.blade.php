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
  <h1>Reportes de Usuarios Registrados</h1>
  <table border="1">
    <thead>
      <tr>
        <th>Id</th>
        <th>Id del rol</th>
        <th>Nombre</th>
        <th>Apellido</th>
        <th>Username</th>
        <th>Email</th>
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
      <td>{{ $item->id }}</td>
      <td>{{ $item->role_id }}</td>
      <td>{{ $item->first_name }}</td>
      <td>{{ $item->last_name }}</td>
      <td>{{ $item->username }}</td>
      <td>{{ $item->email }}</td>
      </tr>
    @endforeach
    @endif
    </tbody>
  </table>
</body>

</html>