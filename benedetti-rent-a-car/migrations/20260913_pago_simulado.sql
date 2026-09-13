ALTER TABLE reservas MODIFY codigo_reserva VARCHAR(50) DEFAULT NULL;
ALTER TABLE pagos MODIFY metodo_pago ENUM('paypal','stripe','payu','wompi','mercadopago','efectivo','transferencia','pendiente','tarjeta','pse','qr') NOT NULL;
