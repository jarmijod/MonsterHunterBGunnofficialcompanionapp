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
    <h1 class="mb-4">!Bienvenido a Astera <?= htmlspecialchars($_SESSION['Nombre_usuario']) ?>!</h1>
    <h6 class="mb-4">En la aldea podrán realizar diversas actividades para poder estar mejor preparados para sus
        cacerías.</h6>

    <div class="accordion" id="accordionExample">
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingFifth">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapseFifth" aria-expanded="false" aria-controls="collapseFifth">
                    Encargada
                </button>
            </h2>
            <div id="collapseFifth" class="accordion-collapse collapse" aria-labelledby="headingFifth"
                data-bs-parent="#accordionExample">
                <div class="accordion-body">
                    Con la encargada podrás solicitar Misiones y Encargos para recolectar sus respectivas recompensas.

                    <ul>
                        <li>Misiones de Cacería: Da Caza a monstruos para recolectar sus recompensas y progresar en la
                            historia. A medida que tu rango de cazador aumente podrás acceder a nuevas misiones de mayor
                            dificultad.</li>
                        <li>Encargos: Los Encargos ofrecen recompensas materiales por completar ciertos objetivos.
                            Cada jugador puede tener una recompensa a la vez. Las recompensas se completan
                            automáticamente cuando el jugador que las obtuvo logra el objetivo. Las recompensas se
                            otorgan al regresar de una cacería.
                        </li>

                    </ul>
                </div>
            </div>
        </div>
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingOne">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                    Centro de recursos
                </button>
            </h2>
            <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne"
                data-bs-parent="#accordionExample">
                <div class="accordion-body">
                    En este lugar La Encargada del centro de recursos te pedíra materiales para habilitar
                    diversas mejoras en la aldea, desde mejoras en la cantina, en la forja e incluso proyectos de
                    campamentos para las misiones. Accederás a estas mejoras una vez cumplas con el rango y con los
                    materiales solicitados.
                </div>
            </div>
        </div>
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingTwo">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                    Comerciante
                </button>
            </h2>
            <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo"
                data-bs-parent="#accordionExample">
                <div class="accordion-body">
                    Puedes escoger entre las siguientes opciones::
                    <ul>
                        <li> <strong>Intercambiar recursos comunes: </strong> Descarta 3 minerales, huesos o pieles
                            comunes de tu
                            inventario para recibir 1 mineral, hueso, pieles o poción común, u otros a cambio.</li>
                        <li><strong>Intercambiar recursos raros: </strong> Descarta 10 recursos cualquiera de tu
                            inventario para recibir 1
                            recurso de la tabla de recompensas de un monstruo. Solo puedes seleccionar recursos de un
                            monstruo que se haya cazado al menos una vez.</li>
                        <li><strong>Comprar una poción: </strong> Descarta 3 materiales raros para una poción.</li>


                    </ul>
                    Cada opción estára disponible a medida que progrese el centro de recursos.
                </div>
            </div>
        </div>
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingThree">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                    Cantina
                </button>
            </h2>
            <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree"
                data-bs-parent="#accordionExample">
                <div class="accordion-body">
                    El <strong> Chef Miau-sculoso</strong> te ofrecerá una variedad de platillos que te ayudarán a
                    mejorar tus habilidades en las cacerías. La cantina irá ofreciendo platillos con mejores efectos a
                    medida que progreses en el centro de recursos. Solo se puede volver a comer una vez hayas gastado
                    todas las bonificaciones de la comida anterior
                    ( Cada comida tendrá su costo asociado y se entregarán fichas consumibles que representarán las
                    habilidades adquiridas en la comida )
                </div>
            </div>
        </div>
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingFour">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                    Forja
                </button>
            </h2>
            <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour"
                data-bs-parent="#accordionExample">
                <div class="accordion-body">
                    Podrás forjar armas y armaduras nuevas o mejorar las que ya tienes cumpliendo sus respectivos
                    requisitos. A medida que progrese el Centro de recursos podrás acceder a rarezas superiores en la
                    forja.
                </div>
            </div>
        </div>

    </div>
</div>

<?php
// Si usas footer.php que cierre body/html, inclúyelo; si no, omítelo.
include '../includes/footer.php';