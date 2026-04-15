<?php
session_start();


include  '../includes/header.php';

if (!isset($_SESSION['Id_usuario'])) {
    header('Location: ..\login.php');
    exit;
}
$userId = $_SESSION['Id_usuario'];

?>

<div class="container-fluid py-3 px-2">
    <h1 class="mb-4">Ayuda a jugador</h1>

    <div class="accordion" id="accordionPanelsStayOpenExample">
        <div class="accordion-item">
            <h2 class="accordion-header" id="panelsStayOpen-headingOne">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                    data-bs-target="#panelsStayOpen-collapseOne" aria-expanded="false"
                    aria-controls="panelsStayOpen-collapseOne">
                    Reglas basicas
                </button>
            </h2>
            <div id="panelsStayOpen-collapseOne" class="accordion-collapse collapse "
                aria-labelledby="panelsStayOpen-headingOne">
                <div class="accordion-body">
                    <strong>Turno de jugador:</strong>
                    El tu turno se pueden hacer una de las siguientes acciones en cualquier orden:
                    <ul>
                        <strong>Acciones de combate:</strong>
                        <li>Jugar cartas de ataque: Puedes jugar cartas de ataque boca arriba para resolver sus efectos
                        </li>
                        <li>Caminar: Una vez durante tu turno, puedes mover tu cazador a un nodo adyacente</li>
                        <li>Correr: Puedes jugar cartas de ataque boca abajo en tu tablero de resistencia para usar su
                            valor de agilidad y moverte nodos extras</li>
                        <strong>Acciones de preparación:</strong>
                        <li>Usar una Poción: Una vez durante tu turno, puedes usar una poción para recuperar salud y
                            stamina. Necesitas tener una pocion disponible para resolver sus efectos</li>
                        <li>Afilar: Una vez por turno, puedes barajar tus cartas de daño descartadas en tu mazo de daño
                        </li>
                        <li>Caminar: Una vez durante tu turno, puedes mover tu cazador a un nodo adyacente</li>
                        <li>Correr: Puedes jugar cartas de ataque boca abajo en tu tablero de resistencia para usar su
                            valor de agilidad y moverte nodos extras</li>

                    </ul>
                </div>
            </div>
        </div>
        <div class="accordion-item">
            <h2 class="accordion-header" id="panelsStayOpen-headingTwo">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                    data-bs-target="#panelsStayOpen-collapseTwo" aria-expanded="false"
                    aria-controls="panelsStayOpen-collapseTwo">
                    Reglas avanzadas
                </button>
            </h2>
            <div id="panelsStayOpen-collapseTwo" class="accordion-collapse collapse"
                aria-labelledby="panelsStayOpen-headingTwo">
                <div class="accordion-body">
                    <li> <strong>Bonus de cambio de arma</strong>
                        La Comisión de Investigación quiere que domines todas las clases de armas. Usar una clase de
                        arma que no hayas usado en las últimas 3 cacerías te otorgará un dado adicional durante la
                        adquisición de recompensas al final de una cacería.</li>
                    <li> <strong>Desmayo</strong>
                        Cuando te desmayes, descarta inmediatamente una carta de tiempo. Puedes reaparecer en uno de los
                        puntos de aparicion en cualquier momento siempre que tu ficha de amenaza este disponible. Cuando
                        lo hagas decidiras cambiar cartas de tiempo por preparacion para el combate, una carta de tiempo
                        para afilar el arma, una carta de tiempo por curarte, una carta de tiempo para vaciar tu tablero
                        de resistencia</li>
                    <li> <strong>Cartas de tiempo</strong>
                        Rugido: Los jugadores pueden colocar 2 cartas boca abajo en el tablero de resistencia para
                        reducir la pérdida de cartas. Varios jugadores pueden colocar varias cartas.

                        Sigan cazando: El maestro de caza podrá usar esta carta para proponer un evento tematico que
                        ajuste la dificultad de la Cacería.

                        Copas venenosas y sapos: Si fallas en la tirada de dados puedes colocar una carta de ataque boca
                        abajo para repetir la tirada, este proceso se puede repetir cuantas veces pueda y quiera el.
                        jugador.
                    </li>
                </div>
            </div>
        </div>
        <div class="accordion-item">
            <h2 class="accordion-header" id="panelsStayOpen-headingThree">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                    data-bs-target="#panelsStayOpen-collapseThree" aria-expanded="false"
                    aria-controls="panelsStayOpen-collapseThree">
                    Accordion Item #3
                </button>
            </h2>
            <div id="panelsStayOpen-collapseThree" class="accordion-collapse collapse"
                aria-labelledby="panelsStayOpen-headingThree">
                <div class="accordion-body">
                    <strong>This is the third item's accordion body.</strong> It is hidden by default, until the
                    collapse plugin adds the appropriate classes that we use to style each element. These classes
                    control the overall appearance, as well as the showing and hiding via CSS transitions. You can
                    modify any of this with custom CSS or overriding our default variables. It's also worth noting that
                    just about any HTML can go within the <code>.accordion-body</code>, though the transition does limit
                    overflow.
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Si usas footer.php que cierre body/html, inclúyelo; si no, omítelo.
include '../includes/footer.php';