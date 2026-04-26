<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // NOTE: Prioridad de los estados de Vehicle: out_of_service > maintenance > reserved > available

    public function up(): void
    {
        // HACK: Function 1
        // - Verifica si un vehículo está disponible para un rango de fecha/hora
        // - Retorna FALSE si:
        //      1. El vehículo está out_of_service
        //      2. El vehículo tiene un mantenimiento abierto
        //      3. Existe una solicitud aprobada cuyo rango se traslapa con el solicitado
        // - Retorna TRUE si ninguna de las condiciones anteriores se cumple
        // - El parametro exclude_request_id es opcional (NULL por defecto)
        //   se usa al momento de aprobar una solicitud existente para excluirla del conteo de solapamiento
        //   pues de lo contrario, la solicitud que se está aprobando se contaría a sí misma
        DB::statement("
            CREATE OR REPLACE FUNCTION fn_is_vehicle_available(
                p_vehicle_id         BIGINT,
                p_start_at           TIMESTAMP,
                p_end_at             TIMESTAMP,
                p_exclude_request_id BIGINT DEFAULT NULL
            )
            RETURNS BOOLEAN AS $$
            DECLARE
                f_vehicle_status    VARCHAR;
                f_open_maintenance  INTEGER;
                f_overlapping       INTEGER;
            BEGIN
                -- Validacion 1: el rango de fecha debe ser valido
                -- La fecha/hora de inicio debe ser obligatoriamente menor a la final
                IF p_start_at >= p_end_at THEN
                    RETURN FALSE;
                END IF;

                -- Validacion 2: verificar estado del vehículo
                SELECT status INTO f_vehicle_status
                FROM vehicles
                WHERE id = p_vehicle_id
                    AND deleted_at IS NULL;

                -- Si el vehículo no existe o está out_of_service
                IF f_vehicle_status IS NULL OR f_vehicle_status = 'out_of_service' THEN
                    RETURN FALSE;
                END IF;

                -- Validacion 3: verificar mantenimientos abiertos
                SELECT COUNT(*) INTO f_open_maintenance
                FROM maintenances
                WHERE vehicle_id = p_vehicle_id
                    AND status = 'open'
                    AND deleted_at IS NULL;

                IF f_open_maintenance > 0 THEN
                    RETURN FALSE;
                END IF;

                -- Validacion 4: verificar solapamiento con solicitudes aprobadas
                -- Cubre los 4 casos de solapamiento:
                --   Caso 1: start_at < p_end_at AND end_at > p_start_at
                --   Esto es suficiente para cubrir solapamiento parcial izquierdo,
                --   parcial derecho, contenido dentro y contenedor
                SELECT COUNT(*) INTO f_overlapping
                FROM vehicle_requests
                WHERE vehicle_id = p_vehicle_id
                    AND status = 'approved'
                    AND start_at < p_end_at -- fecha de inicio menor a fecha final de la solicitud
                    AND end_at > p_start_at -- fecha final mayor a la fecha de inicio de la solicitud
                    AND (p_exclude_request_id IS NULL OR id <> p_exclude_request_id)
                    AND deleted_at IS NULL;

                IF f_overlapping > 0 THEN
                    RETURN FALSE;
                END IF;

                -- Todas las validaciones pasaron
                RETURN TRUE;

            END;
            $$ LANGUAGE plpgsql;
        ");

        // HACK: Function 2
        // - Calcula los kilómetros recorridos en un viaje
        // - Retorna la diferencia entre el kilometraje de retorno y el de salida
        // - Regla de negocio: kilometraje de retorno no puede ser menor al de salida
        // - Retorna 0 si el viaje no ha retornado (NULL) o si los datos son inconsistentes
        DB::statement("
            CREATE OR REPLACE FUNCTION fn_calculate_km_driven(
                p_departure_mileage INTEGER,
                p_return_mileage    INTEGER
            )
            RETURNS INTEGER AS $$
            BEGIN
                IF p_return_mileage IS NULL OR p_return_mileage < p_departure_mileage THEN
                    RETURN 0;
                END IF;

                RETURN p_return_mileage - p_departure_mileage;
            END;
            $$ LANGUAGE plpgsql;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP FUNCTION IF EXISTS fn_calculate_km_driven");
        DB::statement("DROP FUNCTION IF EXISTS fn_is_vehicle_available");
    }
};
