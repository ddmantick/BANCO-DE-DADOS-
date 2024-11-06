create database tarefas
use Tarefas

create table tbl_usuarios(
       usu_codigo int primary key auto_increment,
       usu_nome varchar (45),
       usu_email varchar (100)
);

create table tbl_tarefas(
       tar_codigo int primary key auto_increment,
       tar_setor varchar (50),
       tar_propriedade varchar (90),
       tar_descrição varchar (100),
       tar_status varchar (60)
);

alter table tbl_tarefas
add column usu_codigo int,
add constraint fk_usu_codigo foreign key (usu_codigo) references tbl_usuarios(usu_codigo);