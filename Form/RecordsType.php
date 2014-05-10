<?php
/**
 * powerdns-api
 * 
 * @author Markus Seifert <m.seifert@syseleven.de>
 */

namespace SysEleven\PowerDnsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use SysEleven\PowerDnsBundle\Entity\Records;

/**
 * @author Markus Seifert <m.seifert@syseleven.de>
 * @package SysEleven\PowerDnsBundle\Form
 */
class RecordsType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder

            ->add('type','text')
            ->add('prio','integer', array('required' => false))
            ->add('ttl','integer', array('required' => false))
            ->add('domain','entity',array('class' => 'SysElevenPowerDnsBundle:Domains'))
            ->add('managed','choice', array('choices' => array('0','1'), 'empty_value' => null, 'required' => false))
            ->add('loose_check','choice', array('choices' => array('0','1'), 'empty_value' => null, 'required' => false));

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $recordObj = $event->getData();
                $form = $event->getForm();

                if ($recordObj && $recordObj->getType() == 'SOA') {
                    $form->add('name', 'text');
                    $form->add('content',new SoaFieldType());
                    return;
                }

                $form->add('name','text');
                $form->add('content','text');
            });

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {

                $data = $event->getData();
                $form = $event->getForm();

                if (!array_key_exists('type', $data)) {
                    $parentData = $event->getForm()->getData();

                    if ($parentData instanceof Records) {
                        $type = $parentData->getType();
                    } else {
                        $type = 'Default';
                    }

                } else {
                    $type = $data['type'];
                }

                if ($type && $type == 'SOA') {
                    $form->add('name', 'text');
                    $form->add('content',new SoaFieldType());
                    return;
                }

                $form->add('name','text');
                $form->add('content','text');
            });


    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'csrf_protection' => false,
                'data_class' => 'SysEleven\PowerDnsBundle\Entity\Records',
            ));
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return '';
    }
}