<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

final class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'form.name',
                'constraints' => [new Assert\NotBlank(), new Assert\Length(max: 100)],
            ])
            ->add('email', EmailType::class, [
                'label' => 'form.email',
                'constraints' => [new Assert\NotBlank(), new Assert\Email(), new Assert\Length(max: 180)],
            ])
            ->add('message', TextareaType::class, [
                'label' => 'form.message',
                'constraints' => [new Assert\NotBlank(), new Assert\Length(min: 10, max: 5000)],
            ])
            // Honeypot: hidden from people with CSS, bots fill it in.
            ->add('website', TextType::class, [
                'required' => false,
                'mapped' => false,
                'label' => false,
                'attr' => ['autocomplete' => 'off', 'tabindex' => '-1'],
                'row_attr' => ['class' => 'visually-hidden', 'aria-hidden' => 'true'],
            ]);
    }
}
