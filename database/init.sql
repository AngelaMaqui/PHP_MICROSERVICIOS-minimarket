CREATE TABLE IF NOT EXISTS Producto (
    id INT NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    proveedor VARCHAR(100) NOT NULL,
    fecha_vencimiento DATE NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    stock INT NOT NULL,
    eliminado TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    CONSTRAINT chk_precio CHECK (precio >= 0),
    CONSTRAINT chk_stock CHECK (stock >= 0)
);

INSERT INTO Producto (nombre, proveedor, fecha_vencimiento, precio, stock) VALUES
('Leche entera 1L', 'PIL Andina', '2026-12-30', 8.50, 40),
('Arroz 1kg', 'Granos del Sur', '2027-06-15', 12.00, 25),
('Galletas de vainilla', 'Dulces Bolivia', '2026-11-20', 5.00, 60);