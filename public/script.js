
document.getElementById("loginForm").addEventListener("submit", function(event) {
    event.preventDefault();
    const username = document.getElementById("username").value;
    const password = document.getElementById("password").value;
    alert(`Bienvenido ${username}`);
});




// Rellenar credenciales de usuario para pruebas
function rellenarCredenciales(usuario, contrasena) {
    document.getElementById('usuario').value = usuario;
    document.getElementById('contrasena').value = contrasena;
}



    

