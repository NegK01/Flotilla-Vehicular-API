<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // HACK: Procedure 1
        // - Aprueba una solicitud de vehículo existente (tipo driver_request)
        // - Valida que la solicitud exista y esté en estado pending
        // - Llama a fn_is_vehicle_available() para validar disponibilidad y solapamiento.
        // - Si todo está bien → cambia solicitud a 'approved' y registra reviewed_by y reviewed_at
        // - El trigger 1 se encarga automáticamente de cambiar el vehículo a 'reserved'
        DB::statement("
            CREATE OR REPLACE PROCEDURE p_approve_vehicle_request(
                p_request_id    BIGINT,
                p_reviewed_by   BIGINT
            )
            LANGUAGE plpgsql
            AS $$
            DECLARE
                v_vehicle_id            BIGINT;
                v_start_at              TIMESTAMP;
                v_end_at                TIMESTAMP;
                v_request_status        VARCHAR;
                v_available             BOOLEAN;
            BEGIN
                -- Paso 1: verificar que la solicitud existe y está pending
                SELECT vehicle_id, start_at, end_at, status
                INTO v_vehicle_id, v_start_at, v_end_at, v_request_status
                FROM vehicle_requests
                WHERE id = p_request_id
                    AND deleted_at IS NULL;

                IF NOT FOUND THEN
                    RAISE EXCEPTION 'La solicitud % no existe o fue eliminada', p_request_id;
                END IF;

                IF v_request_status <> 'pending' THEN
                    RAISE EXCEPTION 'La solicitud % no está en estado pending, estado actual: %', p_request_id, v_request_status;
                END IF;

                -- Paso 2: verificar que el operador existe
                IF NOT EXISTS (
                    SELECT 1 FROM users
                    WHERE id = p_reviewed_by
                        AND deleted_at IS NULL
                ) THEN
                    RAISE EXCEPTION 'El operador % no existe o fue eliminado', p_reviewed_by;
                END IF;

                -- Paso 3: verificar disponibilidad usando la función
                -- Se pasa p_request_id para excluirse a sí misma del conteo de solapamiento
                SELECT fn_is_vehicle_available(v_vehicle_id, v_start_at, v_end_at, p_request_id)
                INTO v_available;

                IF NOT v_available THEN
                    RAISE EXCEPTION 'El vehículo % no está disponible para el rango solicitado.', v_vehicle_id;
                END IF;

                -- Paso 4: aprobar la solicitud
                UPDATE vehicle_requests
                SET status      = 'approved',
                    reviewed_by = p_reviewed_by,
                    reviewed_at = NOW()
                WHERE id = p_request_id;

                -- El trigger 1 cambia automáticamente el vehículo a 'reserved'

            END;
            $$
        ");

        // HACK: Procedure 2
        // - Crea una asignación directa de un vehículo a un chofer sin solicitud previa del chofer
        // - El operador asigna directamente, la solicitud nace directamente como 'approved'
        // - Valida que el chofer y el operador existan
        // - Llama a fn_is_vehicle_available() para validar disponibilidad y solapamiento
        // - Si todo está bien → inserta vehicle_request con status = 'approved' y tipo 'direct_assignment'
        // - El trigger 1 se encarga automáticamente de cambiar el vehículo a 'reserved'
        // - El operador luego crea el trip asociado a esta vehicle_request desde el controller
        DB::statement("
            CREATE OR REPLACE PROCEDURE p_direct_assignment(
                p_vehicle_id    BIGINT,
                p_driver_id     BIGINT,
                p_start_at      TIMESTAMP,
                p_end_at        TIMESTAMP,
                p_reviewed_by   BIGINT,
                p_observation   TEXT DEFAULT NULL
            )
            LANGUAGE plpgsql
            AS $$
            DECLARE
                v_available     BOOLEAN;
            BEGIN
                -- Paso 1: validar el rango de fechas
                -- La fecha/hora de inicio debe ser obligatoriamente menor a la final
                IF p_start_at >= p_end_at THEN
                    RAISE EXCEPTION 'La fecha de inicio debe ser menor a la fecha de fin';
                END IF;

                -- Paso 2: verificar que el chofer existe
                IF NOT EXISTS (
                    SELECT 1 FROM users
                    WHERE id = p_driver_id
                        AND deleted_at IS NULL
                ) THEN
                    RAISE EXCEPTION 'El chofer % no existe o fue eliminado', p_driver_id;
                END IF;

                -- Paso 3: verificar que el operador existe
                IF NOT EXISTS (
                    SELECT 1 FROM users
                    WHERE id = p_reviewed_by
                        AND deleted_at IS NULL
                ) THEN
                    RAISE EXCEPTION 'El operador % no existe o fue eliminado', p_reviewed_by;
                END IF;

                -- Paso 4: verificar disponibilidad usando la función
                -- No se pasa exclude_request_id porque la solicitud aún no existe
                SELECT fn_is_vehicle_available(p_vehicle_id, p_start_at, p_end_at)
                INTO v_available;

                IF NOT v_available THEN
                    RAISE EXCEPTION 'El vehículo % no está disponible para el rango solicitado', p_vehicle_id;
                END IF;

                -- Paso 5: insertar la asignación directa como aprobada
                INSERT INTO vehicle_requests (
                    driver_id,
                    vehicle_id,
                    start_at,
                    end_at,
                    status,
                    observation,
                    reviewed_by,
                    reviewed_at,
                    request_type,
                    created_at,
                    updated_at
                ) VALUES (
                    p_driver_id,
                    p_vehicle_id,
                    p_start_at,
                    p_end_at,
                    'approved',
                    p_observation,
                    p_reviewed_by,
                    NOW(),
                    'direct_assignment',
                    NOW(),
                    NOW()
                );

                -- El trigger 1 cambia automáticamente el vehículo a 'reserved'

            END;
            $$
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP PROCEDURE IF EXISTS p_direct_assignment");
        DB::statement("DROP PROCEDURE IF EXISTS p_approve_vehicle_request");
    }
};
