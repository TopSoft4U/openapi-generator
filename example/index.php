<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Swagger UI</title>
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/swagger-ui-dist@3/swagger-ui.css" >
    <style>
        html
        {
            box-sizing: border-box;
            overflow: -moz-scrollbars-vertical;
            overflow-y: scroll;
        }
        *,
        *:before,
        *:after
        {
            box-sizing: inherit;
        }

        body {
            margin:0;
            background: #fafafa;
        }
    </style>
</head>

<body>

<div id="swagger-ui-container"></div>

<script src="https://unpkg.com/swagger-ui-dist@3/swagger-ui-bundle.js">
</script>
<script>
  const i = 0

  function displayDefinitions(definitionUrls) {
    definitionUrls.forEach((url, i) => {
      const newElement = document.createElement("div")
      const divider = document.createElement("hr")
      newElement.id = "swagger-ui-" + i
      const container = document.querySelector("#swagger-ui-container")

      container.appendChild(newElement)
      container.appendChild(divider)
      SwaggerUIBundle({
        url: url,
        dom_id: '#swagger-ui-' + i
      })
      i++
    })
  }
</script>


<script>
  displayDefinitions([
    // URL to your exposed openapi.php file from this example folder
    "http://localhost/example/openapi.php",
  ])
</script>
</body>

</html>