<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Documentation - Gestion des Congés</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        .header {
            background: #2d3748;
            color: white;
            padding: 1rem 2rem;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header h1 {
            margin: 0;
            font-size: 1.5rem;
        }
        .header p {
            margin: 0.5rem 0 0 0;
            opacity: 0.8;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🏖️ API Gestion des Congés</h1>
        <p>Documentation interactive des endpoints - Version 1.0</p>
    </div>
    
    <rapi-doc 
        spec-url="/api/openapi.json" 
        theme="light"
        render-style="view"
        layout="row"
        schema-style="table"
        primary-color="#3182ce"
        secondary-color="#2d3748"
        nav-bg-color="#f7fafc"
        nav-text-color="#2d3748"
        nav-hover-bg-color="#e2e8f0"
        nav-hover-text-color="#1a202c"
        nav-accent-color="#3182ce"
        
        regular-font="system-ui, -apple-system, BlinkMacSystemFont, Segoe UI, Roboto"
        mono-font="SFMono-Regular, Monaco, Consolas, Liberation Mono, Courier New, monospace"
        
        show-header="false"
        show-info="true"
        show-components="true"
        show-curl-before-try="true"
        
        allow-authentication="true"
        allow-server-selection="true"
        allow-search="true"
        allow-try="true"
        
        default-api-server="http://localhost"
        server-url="http://localhost"
        
        nav-item-spacing="relaxed"
        use-path-in-nav-bar="true"
        show-method-in-nav-bar="as-colored-text"
        
        schema-expand-level="1"
        schema-description-expanded="true"
        default-schema-tab="example"
        
        response-area-height="400px"
        
        sort-tags="true"
        sort-endpoints-by="method"
        
        fill-request-fields-with-example="true"
        persist-auth="true"
        fetch-credentials="include"
        
        style="height: calc(100vh - 120px); width: 100%;"
    >
    </rapi-doc>
    
    <script src="https://unpkg.com/rapidoc/dist/rapidoc-min.js"></script>
    
    <script>
        // Configuration pour Docker
        document.addEventListener('DOMContentLoaded', function() {
            const rapidoc = document.querySelector('rapi-doc');
            
            // Détecter si on est en local ou en Docker
            const isDocker = window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1';
            const baseUrl = isDocker ? window.location.origin : 'http://localhost';
            
            // Configurer l'URL du serveur dynamiquement
            rapidoc.setAttribute('server-url', baseUrl);
            rapidoc.setAttribute('default-api-server', baseUrl);
            
            console.log('Rapidoc configuré pour:', baseUrl);
        });
    </script>
</body>
</html>
