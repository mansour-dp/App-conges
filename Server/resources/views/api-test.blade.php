<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Test Interface</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        h1, h2 {
            color: #333;
        }
        .endpoint {
            border: 1px solid #ddd;
            border-radius: 4px;
            margin: 10px 0;
            padding: 15px;
            background: #f9f9f9;
        }
        .method {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            color: white;
            font-weight: bold;
            margin-right: 10px;
        }
        .get { background: #61affe; }
        .post { background: #49cc90; }
        .put { background: #fca130; }
        .delete { background: #f93e3e; }
        input, textarea, select {
            width: 100%;
            padding: 8px;
            margin: 5px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background: #0056b3;
        }
        .response {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 15px;
            border-radius: 4px;
            margin-top: 10px;
            white-space: pre-wrap;
            font-family: monospace;
        }
        .token-input {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 API Test Interface</h1>
        <p>Interface simple pour tester votre API de gestion des congés</p>
        
        <div class="token-input">
            <label for="authToken">Token d'authentification (optionnel):</label>
            <input type="text" id="authToken" placeholder="Collez votre token ici après login">
        </div>
    </div>

    <div class="container">
        <h2>🔐 Authentification</h2>
        
        <div class="endpoint">
            <div>
                <span class="method post">POST</span>
                <strong>/api/login</strong>
            </div>
            <div style="margin-top: 10px;">
                <input type="email" id="loginEmail" placeholder="Email (admin@app.com)" value="admin@app.com">
                <input type="password" id="loginPassword" placeholder="Mot de passe" value="password">
                <button onclick="login()">Se connecter</button>
            </div>
            <div id="loginResponse" class="response" style="display: none;"></div>
        </div>

        <div class="endpoint">
            <div>
                <span class="method get">GET</span>
                <strong>/api/user</strong>
            </div>
            <div style="margin-top: 10px;">
                <button onclick="getUser()">Obtenir utilisateur connecté</button>
            </div>
            <div id="getUserResponse" class="response" style="display: none;"></div>
        </div>

        <div class="endpoint">
            <div>
                <span class="method post">POST</span>
                <strong>/api/logout</strong>
            </div>
            <div style="margin-top: 10px;">
                <button onclick="logout()">Se déconnecter</button>
            </div>
            <div id="logoutResponse" class="response" style="display: none;"></div>
        </div>
    </div>

    <div class="container">
        <h2>📊 Dashboard</h2>
        
        <div class="endpoint">
            <div>
                <span class="method get">GET</span>
                <strong>/api/dashboard/stats</strong>
            </div>
            <div style="margin-top: 10px;">
                <button onclick="getDashboardStats()">Obtenir les statistiques</button>
            </div>
            <div id="dashboardStatsResponse" class="response" style="display: none;"></div>
        </div>
    </div>

    <div class="container">
        <h2>👥 Utilisateurs</h2>
        
        <div class="endpoint">
            <div>
                <span class="method get">GET</span>
                <strong>/api/users</strong>
            </div>
            <div style="margin-top: 10px;">
                <button onclick="getUsers()">Lister les utilisateurs</button>
            </div>
            <div id="usersResponse" class="response" style="display: none;"></div>
        </div>
    </div>

    <div class="container">
        <h2>🏖️ Demandes de congés</h2>
        
        <div class="endpoint">
            <div>
                <span class="method get">GET</span>
                <strong>/api/demandes-conges</strong>
            </div>
            <div style="margin-top: 10px;">
                <button onclick="getDemandesConges()">Lister les demandes</button>
            </div>
            <div id="demandesCongesResponse" class="response" style="display: none;"></div>
        </div>
    </div>

    <div class="container">
        <h2>🧪 Test Simple</h2>
        
        <div class="endpoint">
            <div>
                <span class="method get">GET</span>
                <strong>/api/test</strong>
            </div>
            <div style="margin-top: 10px;">
                <button onclick="testApi()">Tester l'API</button>
            </div>
            <div id="testResponse" class="response" style="display: none;"></div>
        </div>
    </div>

    <script>
        const baseUrl = 'http://localhost';
        
        function getAuthHeaders() {
            const token = document.getElementById('authToken').value;
            return token ? {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            } : {
                'Content-Type': 'application/json'
            };
        }

        function showResponse(elementId, response, data) {
            const element = document.getElementById(elementId);
            element.style.display = 'block';
            element.textContent = `Status: ${response.status}\n\n${JSON.stringify(data, null, 2)}`;
        }

        async function login() {
            const email = document.getElementById('loginEmail').value;
            const password = document.getElementById('loginPassword').value;
            
            try {
                const response = await fetch(`${baseUrl}/api/login`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ email, password })
                });
                
                const data = await response.json();
                showResponse('loginResponse', response, data);
                
                if (data.token) {
                    document.getElementById('authToken').value = data.token;
                    alert('Connexion réussie ! Token ajouté automatiquement.');
                }
            } catch (error) {
                showResponse('loginResponse', { status: 'ERROR' }, { error: error.message });
            }
        }

        async function getUser() {
            try {
                const response = await fetch(`${baseUrl}/api/user`, {
                    headers: getAuthHeaders()
                });
                
                const data = await response.json();
                showResponse('getUserResponse', response, data);
            } catch (error) {
                showResponse('getUserResponse', { status: 'ERROR' }, { error: error.message });
            }
        }

        async function logout() {
            try {
                const response = await fetch(`${baseUrl}/api/logout`, {
                    method: 'POST',
                    headers: getAuthHeaders()
                });
                
                const data = await response.json();
                showResponse('logoutResponse', response, data);
                
                if (response.ok) {
                    document.getElementById('authToken').value = '';
                    alert('Déconnexion réussie !');
                }
            } catch (error) {
                showResponse('logoutResponse', { status: 'ERROR' }, { error: error.message });
            }
        }

        async function getDashboardStats() {
            try {
                const response = await fetch(`${baseUrl}/api/dashboard/stats`, {
                    headers: getAuthHeaders()
                });
                
                const data = await response.json();
                showResponse('dashboardStatsResponse', response, data);
            } catch (error) {
                showResponse('dashboardStatsResponse', { status: 'ERROR' }, { error: error.message });
            }
        }

        async function getUsers() {
            try {
                const response = await fetch(`${baseUrl}/api/users`, {
                    headers: getAuthHeaders()
                });
                
                const data = await response.json();
                showResponse('usersResponse', response, data);
            } catch (error) {
                showResponse('usersResponse', { status: 'ERROR' }, { error: error.message });
            }
        }

        async function getDemandesConges() {
            try {
                const response = await fetch(`${baseUrl}/api/demandes-conges`, {
                    headers: getAuthHeaders()
                });
                
                const data = await response.json();
                showResponse('demandesCongesResponse', response, data);
            } catch (error) {
                showResponse('demandesCongesResponse', { status: 'ERROR' }, { error: error.message });
            }
        }

        async function testApi() {
            try {
                const response = await fetch(`${baseUrl}/api/test`);
                const data = await response.json();
                showResponse('testResponse', response, data);
            } catch (error) {
                showResponse('testResponse', { status: 'ERROR' }, { error: error.message });
            }
        }
    </script>
</body>
</html>
