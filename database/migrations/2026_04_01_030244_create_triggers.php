<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // NOTE: Prioridad de los estados de Vehicle: out_of_service > maintenance > reserved > available

    public function up(): void
    {
        // HACK: Trigger 1 
        // - Reservar el vehiculo al aprobar una solicitud (asignacion directa o solicitud de chofer)
        // - Si el vehículo está out_of_service o en maintenance, no se toca su estado, pues estos son de un una prioridad mayor que reserved
        DB::statement("
            CREATE OR REPLACE FUNCTION fn_reserve_vehicle_on_approval()
            RETURNS TRIGGER AS $$
            DECLARE
                current_vehicle_status VARCHAR;
            BEGIN
                IF (TG_OP = 'INSERT' AND NEW.status = 'approved') OR
                    (TG_OP = 'UPDATE' AND OLD.status <> 'approved' AND NEW.status = 'approved') THEN

                    SELECT status INTO current_vehicle_status
                    FROM vehicles
                    WHERE id = NEW.vehicle_id;

                    IF current_vehicle_status NOT IN ('out_of_service', 'maintenance') THEN
                        UPDATE vehicles SET status = 'reserved'
                        WHERE id = NEW.vehicle_id;
                    END IF;

                END IF;
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;
        ");
        DB::statement("
            CREATE TRIGGER trg_reserve_vehicle_on_approval
            AFTER INSERT OR UPDATE ON vehicle_requests
            FOR EACH ROW
            EXECUTE FUNCTION fn_reserve_vehicle_on_approval();
        ");

        // HACK: Trigger 2 
        // - Al cancelar una solicitud aprobada, determinar el estado del vehículo
        // - Tanto solicitudes aprobadas como viajes activos significan que el vehículo sigue reservado
        // - IDs omitidos: id <> NEW.id en vehicle_requests (esto para evitar que el contador sume la cancelacion que se esta tratando de hacer)
        DB::statement("
            CREATE OR REPLACE FUNCTION fn_release_vehicle_on_cancellation()
            RETURNS TRIGGER AS $$
            DECLARE
                open_maintenances         INTEGER;
                active_requests           INTEGER;
                active_trips              INTEGER;
                current_vehicle_status    VARCHAR;
            BEGIN
                IF OLD.status = 'approved' AND NEW.status = 'cancelled' THEN

                    SELECT status INTO current_vehicle_status
                    FROM vehicles
                    WHERE id = NEW.vehicle_id;

                    -- Prioridad 1: fuera de servicio — no se toca nada
                    IF current_vehicle_status = 'out_of_service' THEN
                        RETURN NEW;
                    END IF;

                    SELECT COUNT(*) INTO open_maintenances
                    FROM maintenances
                    WHERE vehicle_id = NEW.vehicle_id
                        AND status = 'open'
                        AND deleted_at IS NULL;

                    SELECT COUNT(*) INTO active_requests
                    FROM vehicle_requests
                    WHERE vehicle_id = NEW.vehicle_id
                        AND status = 'approved'
                        AND id <> NEW.id
                        AND deleted_at IS NULL;

                    SELECT COUNT(*) INTO active_trips
                    FROM trips
                    WHERE vehicle_id = NEW.vehicle_id
                        AND return_mileage IS NULL
                        AND deleted_at IS NULL;

                    -- Prioridad 2: mantenimiento abierto
                    IF open_maintenances > 0 THEN
                        UPDATE vehicles SET status = 'maintenance'
                        WHERE id = NEW.vehicle_id;

                    -- Prioridad 3: solicitudes aprobadas OR viajes activos
                    ELSIF active_requests > 0 OR active_trips > 0 THEN
                        UPDATE vehicles SET status = 'reserved'
                        WHERE id = NEW.vehicle_id;

                    -- Prioridad 4: ninguna condición anterior — disponible
                    ELSE
                        UPDATE vehicles SET status = 'available'
                        WHERE id = NEW.vehicle_id;
                    END IF;

                END IF;
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;
        ");
        DB::statement("
            CREATE TRIGGER trg_release_vehicle_on_cancellation
            AFTER UPDATE ON vehicle_requests
            FOR EACH ROW
            EXECUTE FUNCTION fn_release_vehicle_on_cancellation();
        ");

        // HACK: Trigger 3
        // - Al abrir un mantenimiento, cambiar vehículo a maintenance
        // - Prioridad: out_of_service no se pisa
        DB::statement("
            CREATE OR REPLACE FUNCTION fn_block_vehicle_on_maintenance()
            RETURNS TRIGGER AS $$
            DECLARE
                current_vehicle_status VARCHAR;
            BEGIN
                IF NEW.status = 'open' THEN

                    SELECT status INTO current_vehicle_status
                    FROM vehicles
                    WHERE id = NEW.vehicle_id;

                    -- Prioridad 1: fuera de servicio — no se toca nada
                    IF current_vehicle_status <> 'out_of_service' THEN
                        UPDATE vehicles SET status = 'maintenance'
                        WHERE id = NEW.vehicle_id;
                    END IF;

                END IF;
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;
        ");
        DB::statement("
            CREATE TRIGGER trg_block_vehicle_on_maintenance
            AFTER INSERT ON maintenances
            FOR EACH ROW
            EXECUTE FUNCTION fn_block_vehicle_on_maintenance();
        ");

        // HACK: Trigger 4
        // - Al cerrar un mantenimiento, determinar el estado del vehículo
        // - Tanto solicitudes aprobadas como viajes activos significan que el vehículo sigue reservado
        // - IDs omitidos: id <> NEW.id en maintenances (esto para evitar que el contador sume la cancelacion que se esta tratando de hacer)

        DB::statement("
            CREATE OR REPLACE FUNCTION fn_release_vehicle_on_maintenance_close()
            RETURNS TRIGGER AS $$
            DECLARE
                open_maintenances         INTEGER;
                active_requests           INTEGER;
                active_trips              INTEGER;
                current_vehicle_status    VARCHAR;
            BEGIN
                IF OLD.status = 'open' AND NEW.status = 'closed' THEN

                    SELECT status INTO current_vehicle_status
                    FROM vehicles
                    WHERE id = NEW.vehicle_id;

                    -- Prioridad 1: fuera de servicio — no se toca nada
                    IF current_vehicle_status = 'out_of_service' THEN
                        RETURN NEW;
                    END IF;

                    SELECT COUNT(*) INTO open_maintenances
                    FROM maintenances
                    WHERE vehicle_id = NEW.vehicle_id
                        AND status = 'open'
                        AND id <> NEW.id
                        AND deleted_at IS NULL;

                    SELECT COUNT(*) INTO active_requests
                    FROM vehicle_requests
                    WHERE vehicle_id = NEW.vehicle_id
                        AND status = 'approved'
                        AND deleted_at IS NULL;

                    SELECT COUNT(*) INTO active_trips
                    FROM trips
                    WHERE vehicle_id = NEW.vehicle_id
                        AND return_mileage IS NULL
                        AND deleted_at IS NULL;

                    -- Prioridad 2: siguen habiendo mantenimientos abiertos
                    IF open_maintenances > 0 THEN
                        UPDATE vehicles SET status = 'maintenance'
                        WHERE id = NEW.vehicle_id;

                    -- Prioridad 3: solicitudes aprobadas OR viajes activos
                    ELSIF active_requests > 0 OR active_trips > 0 THEN
                        UPDATE vehicles SET status = 'reserved'
                        WHERE id = NEW.vehicle_id;

                    -- Prioridad 4: ninguna condición anterior — disponible
                    ELSE
                        UPDATE vehicles SET status = 'available'
                        WHERE id = NEW.vehicle_id;
                    END IF;

                END IF;
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;
        ");
        DB::statement("
            CREATE TRIGGER trg_release_vehicle_on_maintenance_close
            AFTER UPDATE ON maintenances
            FOR EACH ROW
            EXECUTE FUNCTION fn_release_vehicle_on_maintenance_close();
        ");

        // HACK: Trigger 5
        // - Al registrar un regreso del viaje, actualizar kilometraje y determinar el estado del vehículo
        // - Tanto solicitudes aprobadas como otros viajes activos significan que el vehículo sigue reservado
        // - El kilometraje siempre se actualiza sin importar el estado del vehículo
        // - IDs omitidos: 
        //                  - id <> NEW.vehicle_request_id en vehicle_requests (esto para evitar que el contador sume la solicitud que esta asociada al viaje pues seguramente se encuentre aprobada)
        //                  - id <> NEW.id en trips (esto para evitar que el contador sume la cancelacion que se esta tratando de hacer)
        DB::statement("
            CREATE OR REPLACE FUNCTION fn_update_vehicle_mileage_on_return()
            RETURNS TRIGGER AS $$
            DECLARE
                open_maintenances          INTEGER;
                active_requests            INTEGER;
                active_other_trips         INTEGER;
                current_vehicle_status     VARCHAR;
            BEGIN
                IF OLD.return_mileage IS NULL AND NEW.return_mileage IS NOT NULL THEN

                    SELECT status INTO current_vehicle_status
                    FROM vehicles
                    WHERE id = NEW.vehicle_id;

                    -- Prioridad 1: fuera de servicio — solo actualiza kilometraje, no toca status
                    IF current_vehicle_status = 'out_of_service' THEN
                        UPDATE vehicles
                        SET current_mileage = NEW.return_mileage
                        WHERE id = NEW.vehicle_id;

                    ELSE
                        SELECT COUNT(*) INTO open_maintenances
                        FROM maintenances
                        WHERE vehicle_id = NEW.vehicle_id
                            AND status = 'open'
                            AND deleted_at IS NULL;

                        SELECT COUNT(*) INTO active_requests
                        FROM vehicle_requests
                        WHERE vehicle_id = NEW.vehicle_id
                            AND status = 'approved'
                            AND id <> NEW.vehicle_request_id
                            AND deleted_at IS NULL;

                        -- En la teoria, no deberia existir este contador ni validacion, pues el sistema deberia de bloquear el uso de un mismo vehiculo para más de 1 viaje, si se hace, este bloque debe de ser eliminado
                        SELECT COUNT(*) INTO active_other_trips
                        FROM trips
                        WHERE vehicle_id = NEW.vehicle_id
                            AND return_mileage IS NULL
                            AND id <> NEW.id
                            AND deleted_at IS NULL;

                        -- Prioridad 2: mantenimiento abierto
                        IF open_maintenances > 0 THEN
                            UPDATE vehicles
                            SET current_mileage = NEW.return_mileage,
                                status = 'maintenance'
                            WHERE id = NEW.vehicle_id;

                        -- Prioridad 3: solicitudes aprobadas OR otros viajes activos
                        ELSIF active_requests > 0 OR active_other_trips > 0 THEN
                            UPDATE vehicles
                            SET current_mileage = NEW.return_mileage,
                                status = 'reserved'
                            WHERE id = NEW.vehicle_id;

                        -- Prioridad 4: ninguna condición anterior — disponible
                        ELSE
                            UPDATE vehicles
                            SET current_mileage = NEW.return_mileage,
                                status = 'available'
                            WHERE id = NEW.vehicle_id;
                        END IF;
                    END IF;

                END IF;
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;
        ");
        DB::statement("
            CREATE TRIGGER trg_update_vehicle_mileage_on_return
            AFTER UPDATE ON trips
            FOR EACH ROW
            EXECUTE FUNCTION fn_update_vehicle_mileage_on_return();
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Trigger 5
        DB::statement("DROP TRIGGER IF EXISTS trg_update_vehicle_mileage_on_return ON trips");
        DB::statement("DROP FUNCTION IF EXISTS fn_update_vehicle_mileage_on_return");

        // Trigger 4
        DB::statement("DROP TRIGGER IF EXISTS trg_release_vehicle_on_maintenance_close ON maintenances");
        DB::statement("DROP FUNCTION IF EXISTS fn_release_vehicle_on_maintenance_close");

        // Trigger 3
        DB::statement("DROP TRIGGER IF EXISTS trg_block_vehicle_on_maintenance ON maintenances");
        DB::statement("DROP FUNCTION IF EXISTS fn_block_vehicle_on_maintenance");

        // Trigger 2
        DB::statement("DROP TRIGGER IF EXISTS trg_release_vehicle_on_cancellation ON vehicle_requests");
        DB::statement("DROP FUNCTION IF EXISTS fn_release_vehicle_on_cancellation");

        // Trigger 1
        DB::statement("DROP TRIGGER IF EXISTS trg_reserve_vehicle_on_approval ON vehicle_requests");
        DB::statement("DROP FUNCTION IF EXISTS fn_reserve_vehicle_on_approval");
    }
};
