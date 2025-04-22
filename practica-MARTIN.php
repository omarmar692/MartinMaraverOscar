<?php

namespace DAWUD05\TAREA05\modelo;

use TAREA04\modelo\IGuardableOMM;

/**
 * ESTA ES UNA MODIFICACIÓN PARA COMPROBAR EL CONTROL DE VERSIONES CON GIT
 * 
 * Clase que permite crear instancias en las que sa almacenan datos 
 * relativos a un libro como son autor, titulo, fecha de publicación.
 * Implementa una serie de métodos definidos en la interfaz IGuardable
 * como son guardar, listar o borrar, así como otro para verificar si un 
 * codigo ISBN concreto existe o no en la BD con la que trabaja la clase.
 * No cuenta con un metodo constructor en el que se pasen los parámetros.
 * 
 * Ejemplo: $lib=new Libro();.
 *
 * @author Oscar Martín Maraver
 */

class Libro implements IGuardableOMM
{
    private ?int $id = null;
    private ?string $isbn = null;
    private ?string $titulo = null;
    private ?string $autor = null;
    private ?int $anioPub = null;
    private ?int $paginas = null;
    private ?int $ejemDispo = null;
    private $fechaCreacion = null;
    private $fechaActualizacion = null;

    public function setIsbn(string $isbn)
    {
        $this->isbn = $isbn;
    }

    public function setTitulo(string $titulo)
    {
        $this->titulo = $titulo;
    }

    public function setAnioPub(int $anioPub)
    {
        $this->anioPub = $anioPub;
    }

    public function setPaginas(int $paginas)
    {
        $this->paginas = $paginas;
    }

    public function setEjemDispo(int $ejemDispo)
    {
        $this->ejemDispo = $ejemDispo;
    }

    public function setAutor(string $autor)
    {
        $this->autor = $autor;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getIsbn(): string
    {
        return $this->isbn;
    }

    public function getTitulo(): string
    {
        return $this->titulo;
    }

    public function getAutor(): string
    {
        return $this->autor;
    }

    public function getAnioPub(): int
    {
        return $this->anioPub;
    }

    public function getPaginas(): int
    {
        return $this->paginas;
    }

    public function getEjemDispo(): int
    {
        return $this->ejemDispo;
    }

    public function getFechaCreacion()
    {
        return $this->fechaCreacion;
    }

    public function getFechaActualizacion()
    {
        return $this->fechaActualizacion;
    }

    /**
     * La función es una implementación de la declarada en la interfaz IGuardableOMM
     * Su objetivo una vez recibido una instancia de la clase Libro, si los datos son correcto,
     * guardarlo en la BD para lo cual requiere una conexion pasada como parámetro. Los datos de
     * id, fecha_creacion y fecha_actualizacion serán creados directamente en la BD.
     * En caso error devuelve -1 o false depenediendo del tipo de error. En caso de una ejecución correcta
     * devuelve el número de filas insertado en la BD.
     * 
     * @author Oscar Martín Maraver
     * @version 1.0.0
     * @internal Usado para demostración interna de documentación.
     * @param PDO $pdo- conexión a la BD
     * @return int -1 en caso de no ejecutarse la consulta (PDOException)
     * @return bool false en caso de otro error
     */

    public function guardar(\PDO $pdo): int|bool
    {
        if (is_null($this->id)) { //Para poder guardar un id,este debe ser null (indica que aun no se ha insertado y por tanto no se ha generado)
            //hay que recordar que es auto incremental en la base de datos, por lo que no se genera hasta que se inserta
            $SQL = 'INSERT INTO libros (isbn,titulo,autor,anio_publicacion,paginas,ejemplares_disponibles) 
                VALUES (:isbn,:titulo,:autor,:anioPub,:paginas,:ejemDispo)';
            try {
                $stmt = $pdo->prepare($SQL);
                $stmt->bindValue(':isbn', $this->isbn);
                $stmt->bindValue(':titulo', $this->titulo);
                $stmt->bindValue(':autor', $this->autor);
                $stmt->bindValue(':anioPub', $this->anioPub);
                $stmt->bindValue(':paginas', $this->paginas);
                $stmt->bindValue(':ejemDispo', $this->ejemDispo);
                $stmt->execute();
                if ($stmt->rowCount() > 0) //Si se ejecuta, el recuento de filas será mayor de 0
                {
                    $this->id = $pdo->lastInsertId(); //Obtendo el id autogenerado y lo almaceno en el atributo id de la instancia

                    // Obtener las fechas de creación y actualización generadas por la base de datos
                    $SQL_Fecha = 'SELECT fecha_creacion, fecha_actualizacion FROM libros WHERE id = :id';
                    $stmt = $pdo->prepare($SQL_Fecha);
                    $stmt->bindValue(':id', $this->id);
                    $stmt->execute();
                    $datos = $stmt->fetch(\PDO::FETCH_ASSOC); // Devuelve un array asociativo
                    if ($datos) {
                        //Asigno el valor de las fechas obtenidos en la consulta
                        $this->fechaCreacion = $datos['fecha_creacion'];
                        $this->fechaActualizacion = $datos['fecha_actualizacion'];
                    }

                    return $stmt->rowCount();
                }
            } catch (\PDOException $e) {
                return -1;
            }
        }
        return false;
    }

    /**
     * La función es una implementación de la declarada en la interfaz IGuardableOMM
     * Su objetivo recuperar los datos de la BD usando un valor entero pasado como
     * parámetro (id) y crear una instancia de la clase Libro en caso de éxito.
     * En caso error devulve -1 o false depenediendo del tipo de error.
     * 
     * @author Oscar Martín Maraver
     * @version 1.0.0
     * @internal Usado para demostración interna de documentación.
     * @param PDO $pdo - conexión a la BD
     * @param int $id - id del registro que quermos recuperar de la BD
     * @return Libro objeto de la clase
     * @return int -1 en caso de no ejecutarse la consulta (PDOException)
     * @return bool false en caso de otro error
     */

    public static function rescatar(\PDO $pdo, int $id): Libro|int|false
    {
        $SQL = 'SELECT * FROM libros WHERE id=:id';
        try {
            $stmt = $pdo->prepare($SQL);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            $datos = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($datos) {
                $libro = new Libro();
                $libro->id = $datos['id'];
                $libro->isbn = $datos['isbn'];
                $libro->titulo = $datos['titulo'];
                $libro->autor = $datos['autor'];
                $libro->anioPub = $datos['anio_publicacion'];
                $libro->paginas = $datos['paginas'];
                $libro->ejemDispo = $datos['ejemplares_disponibles'];
                $libro->fechaCreacion = $datos['fecha_creacion'];
                $libro->fechaActualizacion = $datos['fecha_actualizacion'];
                return $libro;
            }
        } catch (\PDOException $e) {
            return -1;
        }
        return false;
    }

    /**
     * La función es una implementación de la declarada en la interfaz IGuardableOMM
     * Su objetivo borrar los datos de la BD usando un valor entero pasado como
     * parámetro (id).
     * En caso error devuelve -1 o false depenediendo del tipo de error.
     * 
     * @author Oscar Martín Maraver
     * @version 1.0.0
     * @internal Usado para demostración interna de documentación.
     * @param PDO $pdo - conexión a la BD
     * @param int $id - id del registro que quermos borrar de la BD
     * @return int -1 en caso de no ejecutarse la consulta (PDOException)
     * @return bool false en caso de otro error
     */

    public static function borrar(\PDO $pdo, int $id): bool|int
    {
        $SQL = 'DELETE FROM Libros WHERE id=:id';
        try {
            $stmt = $pdo->prepare($SQL);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                return $stmt->rowCount();
            }
        } catch (\PDOException $e) {
            return -1;
        }
        return false;
    }

    /**
     * La función cuenta cuantos registros hay en la base de datos que cumpla la condición de tener
     * un isbn específico. Como dicho isbn no se puede repetir, solo puede devolver 0 si no existe
     * o 1 si el código existe.
     * 
     * @author Oscar Martín Maraver
     * @version 1.0.0
     * @internal Usado para demostración interna de documentación.
     * @param PDO $pdo - conexión a la BD
     * @param int $isbn - ISBN a buscar en la BD
     * @return int 0 si no existe o 1 si el código existe
     * @return int -1 en caso de no ejecutarse la consulta (PDOException)
     * @return bool false en caso de otro error
     */

    public static function existeISBN(\PDO $pdo, int $isbn): int|false
    {
        $SQL = 'SELECT COUNT(*) FROM libros WHERE isbn=:isbn';
        try {
            $stmt = $pdo->prepare($SQL);
            $stmt->bindValue('isbn', $isbn);

            if ($stmt->execute()) {
                $recuento = $stmt->fetchColumn();
                return $recuento;
            }
        } catch (\PDOException $ex) {
            return -1;
        }
        return false;
    }

     /**
     * La función cuenta cuantos registros hay en la base de datos que cumpla la condición de tener
     * un id específico. Como dicho id no se puede repetir, solo puede devolver 0 si no existe
     * o 1 si el código existe.
     * 
     * @author Oscar Martín Maraver
     * @version 1.0.0
     * @internal Usado para demostración interna de documentación.
     * @param PDO $pdo - conexión a la BD
     * @param int $id - ID a buscar en la BD
     * @return int 0 si no existe o 1 si el código existe
     * @return int -1 en caso de no ejecutarse la consulta (PDOException)
     * @return bool false en caso de otro error
     */

     public static function existeID(\PDO $pdo, int $id): int|false
     {
         $SQL = 'SELECT COUNT(*) FROM libros WHERE id=:id';
         try {
             $stmt = $pdo->prepare($SQL);
             $stmt->bindValue('id', $id);
 
             if ($stmt->execute()) {
                 $recuento = $stmt->fetchColumn();
                 return $recuento;
             }
         } catch (\PDOException $ex) {
             return -1;
         }
         return false;
     }
}
