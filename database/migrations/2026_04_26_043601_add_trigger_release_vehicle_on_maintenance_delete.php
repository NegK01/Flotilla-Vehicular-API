<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // HACK: Trigger 6
        // - Al hacer borrado lógico de un mantenimiento abierto, re-evaluar el estado del vehículo
        // - El Trigger 4 (fn_release_vehicle_on_maintenance_close) solo responde al cambio de
        //   status open→closed. El soft delete (deleted_at: NULL→timestamp) no cambia status,
        //   por lo que el vehículo quedaría atrapado en 'maintenance' indefinidamente.
        // - Misma lógica de prioridad de estados que el Trigger 4
        // - IDs omitidos: id <> NEW.id en maintenances (el registro que se está eliminando)
        DB::statement("
            CREATE OR REPLACE FUNCTION fn_release_vehicle_on_maintenance_delete()
            RETURNS TRIGGER AS $$
            DECLARE
                open_maintenances      INTEGER;
                active_requests        INTEGER;
                active_trips           INTEGER;
                current_vehicle_status VARCHAR;
            BEGIN
                -- Caso 1: Soft Delete (Se elimina un mantenimiento que estaba abierto)
                IF OLD.deleted_at IS NULL AND NEW.deleted_at IS NOT NULL AND OLD.status = 'open' THEN

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

                -- Caso 2: Restore (Se restaura un mantenimiento que estaba abierto)
                ELSIF OLD.deleted_at IS NOT NULL AND NEW.deleted_at IS NULL AND NEW.status = 'open' THEN
                    
                    SELECT status INTO current_vehicle_status
                    FROM vehicles
                    WHERE id = NEW.vehicle_id;

                    -- Si no está fuera de servicio, forzar a 'maintenance'
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
            CREATE TRIGGER trg_release_vehicle_on_maintenance_delete
            AFTER UPDATE ON maintenances
            FOR EACH ROW
            EXECUTE FUNCTION fn_release_vehicle_on_maintenance_delete();
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Trigger 6
        DB::statement("DROP TRIGGER IF EXISTS trg_release_vehicle_on_maintenance_delete ON maintenances");
        DB::statement("DROP FUNCTION IF EXISTS fn_release_vehicle_on_maintenance_delete");
    }
};
