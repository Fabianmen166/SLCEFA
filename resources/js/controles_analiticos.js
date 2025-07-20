  
        document.addEventListener('DOMContentLoaded', function() {
            // Muestra Fortificada - Cálculo masa suelo seco
            document.getElementById('masa_suelo').addEventListener('input', calcularFortificada);
            document.getElementById('humedad_obtenida').addEventListener('input', calcularFortificada);
            document.getElementById('masa_agua').addEventListener('input', calcularFortificada);
            document.getElementById('humedad_fortificada_teorica').addEventListener('input', calcularFortificada);

            function calcularFortificada() {
                const masaSuelo = parseFloat(document.getElementById('masa_suelo').value) || 0;
                const humedadObtenida = parseFloat(document.getElementById('humedad_obtenida').value) || 0;
                const masaAgua = parseFloat(document.getElementById('masa_agua').value) || 0;
                const humedadFortificada = parseFloat(document.getElementById('humedad_fortificada')
                    .value) || 0;

                const masaSueloSeco = masaSuelo * (100 / (humedadObtenida + 100));
                document.getElementById('masa_suelo_seco').value = masaSueloSeco.toFixed(4);

                const humedadTeorica = (((masaSuelo + masaAgua) - masaSueloSeco) / masaSueloSeco) * 100;
                document.getElementById('humedad_fortificada_teorica').value = humedadTeorica.toFixed(2);

                const recuperacion = (humedadFortificada / humedadTeorica) * 100;
                document.getElementById('recuperacion').value = recuperacion.toFixed(2);

                const aceptable = (recuperacion >= 70 && recuperacion <= 130) ? "Aceptable" : "No aceptable";
                document.getElementById('aceptable_fortificada').value = aceptable;
            }

            // Muestra Referencia - Cálculo %REC
            document.getElementById('valor_referencia').addEventListener('input', calcularReferencia);
            document.getElementById('valor_obtenido').addEventListener('input', calcularReferencia);

            function calcularReferencia() {
                const valorReferencia = parseFloat(document.getElementById('valor_referencia').value) || 0;
                const valorObtenido = parseFloat(document.getElementById('valor_obtenido').value) || 0;

                const recuperacionReferencia = Math.abs(valorObtenido) / valorReferencia * 100;
                document.getElementById('recuperacion_referencia').value = recuperacionReferencia.toFixed(2);

                const aceptableRef = (recuperacionReferencia >= 70 && recuperacionReferencia <= 130) ? "Aceptable" :
                    "No aceptable";
                document.getElementById('aceptable_referencia').value = aceptableRef;
            }

            // Duplicado Muestra - Cálculo %DPR
            document.getElementById('humedad_replica_1').addEventListener('input', calcularDuplicado);
            document.getElementById('humedad_replica_2').addEventListener('input', calcularDuplicado);

            function calcularDuplicado() {
                const replica1 = parseFloat(document.getElementById('humedad_replica_1').value) || 0;
                const replica2 = parseFloat(document.getElementById('humedad_replica_2').value) || 0;

                const dpr = Math.abs(replica1 - replica2) / ((replica1 + replica2) / 2) * 100;
                document.getElementById('dpr').value = dpr.toFixed(2);

                const aceptableDup = (dpr < 25) ? "Aceptable" : "No aceptable";
                document.getElementById('aceptable_duplicado').value = aceptableDup;
            }
        });
   
        // Evaluación de aceptabilidad del blanco
        
        function evaluarAceptabilidadBlanco() {
            const resultado = parseFloat(document.getElementById('resultado_blanco').value);
            const lcm = parseFloat(document.getElementById('lcm').value);
            const aceptableField = document.getElementById('aceptable_blanco');

            if (!isNaN(resultado) && !isNaN(lcm)) {
                if (resultado <= lcm) {
                    aceptableField.value = "Aceptable";

                } else {
                    aceptableField.value = "No Aceptable";

                }
            } else {
                aceptableField.value = "";
                aceptableField.style.color = "black";
            }
        }