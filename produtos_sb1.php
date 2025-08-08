<?php
// Configurações da API Protheus
$api_url  = "http://localhost:8080/rest/api";
$username = ""; //Usuário Protheus
$password = ""; //Senha Protheus

// 1. Obter Token
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "$api_url/oauth2/v1/token");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'grant_type' => 'password',
    'username'   => $username,
    'password'   => $password
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/x-www-form-urlencoded'
]);
$response = curl_exec($ch);
if (curl_errno($ch)) {
    die("Erro ao autenticar: " . curl_error($ch));
}
curl_close($ch);

$data = json_decode($response, true);
if (!isset($data['access_token'])) {
    die("Erro ao obter token: " . $response);
}
$token = $data['access_token'];

// 2. Buscar produtos na SB1
$where  = "SB1.D_E_L_E_T_=' '";
$fields = "B1_COD,B1_DESC,B1_TIPO";
$query_url = "$api_url/framework/v1/genericQuery?tables=SB1&fields=$fields&where=" . urlencode($where);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $query_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $token",
    "Content-Type: application/json"
]);
$response = curl_exec($ch);
if (curl_errno($ch)) {
    die("Erro ao buscar produtos: " . curl_error($ch));
}
curl_close($ch);

$produtos = json_decode($response, true);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8" />
  <title>Produtos SB1 - Protheus</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="p-4">

  <h2>Produtos SB1 - Protheus</h2>

  <?php if (!isset($produtos['items']) || count($produtos['items']) === 0): ?>
    <p class="alert alert-warning">Nenhum produto encontrado.</p>
  <?php else: ?>
    <table class="table table-striped table-bordered table-hover">
      <thead class="table-dark">
        <tr>
          <th>Código</th>
          <th>Descrição</th>
          <th>Tipo</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($produtos['items'] as $p):
        $B1_COD  = isset($p['B1_COD']['value']) ? $p['B1_COD']['value'] :
               (isset($p['B1_COD']) ? $p['B1_COD'] :
               (isset($p['b1_cod']) ? $p['b1_cod'] : ''));

    $B1_DESC = isset($p['B1_DESC']['value']) ? $p['B1_DESC']['value'] :
               (isset($p['B1_DESC']) ? $p['B1_DESC'] :
               (isset($p['b1_desc']) ? $p['b1_desc'] : ''));

    $B1_TIPO = isset($p['B1_TIPO']['value']) ? $p['B1_TIPO']['value'] :
               (isset($p['B1_TIPO']) ? $p['B1_TIPO'] :
               (isset($p['b1_tipo']) ? $p['b1_tipo'] : ''));
        ?>
          <tr>
            <td><?= htmlspecialchars($B1_COD) ?></td>
            <td><?= htmlspecialchars($B1_DESC) ?></td>
            <td><?= htmlspecialchars($B1_TIPO) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>

</body>
</html>
