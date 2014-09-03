<?php
    namespace Thin;
    /**
     * @Entity @Table(name="book")
     **/
    class DoctrineBookEntity
    {
        /** @Id @Column(type="integer") @GeneratedValue **/
        protected $id;
        /** @Column(type="string") **/
        protected $name;
        /** @Column(type="text") **/
        protected $description;
        // .. (other code)
    }
