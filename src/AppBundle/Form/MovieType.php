<?php
/**
 * Created by PhpStorm.
 * User: toma4
 * Date: 18.12.2017
 * Time: 14:17
 */

namespace AppBundle\Form;


use AppBundle\Entity\Channel;
use AppBundle\Entity\Movie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MovieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options); // TODO: Change the autogenerated stub

        $builder
            ->add("name",TextType::class, ["label" => "nazwa filmu"])
            ->add("link", TextType::class, ["label" => "link enbed"])
            ->add("description", TextareaType::class, ["label" => "opis"])
            ->add('channel', EntityType::class, array(
                "class" => Channel::class,
                "choice_label" => "name",
                'required'   => false,
                'empty_data' => null
            ))
            ->add("submit", SubmitType::class, ["label" => "zapisz"]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(["data_class" => Movie::class]); // zastepuje w kontrolerze $movie = $form->getData(); - wstawianie danych do klasy film
    }
}